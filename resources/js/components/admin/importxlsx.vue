<template>
  <div>
    <v-snackbar v-model="snackbar" top right timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn
          color="pink"
          text
          v-bind="attrs"
          @click="
            snackbar = false;
            userid = null;
          "
        >
          X
        </v-btn>
      </template>
    </v-snackbar>

    <v-container fluid>
      <v-row>
        <v-col cols="2">
          <v-select
            v-model="selectedProvider"
            :items="providers"
            label="Провайдер"
            item-text="name"
            item-value="id"
            @change="userids = []"
          ></v-select>
          <v-select
            v-if="baers.length"
            v-model="selectedBaer"
            :items="baers"
            label="Баер"
            clearable
          ></v-select>
        </v-col>
        <v-col cols="2" v-if="selectedProvider">
          <v-file-input
            v-model="files"
            ref="fileupload"
            label="загрузить Excel"
            show-size
            truncate-length="24"
            @change="onFileChange"
          ></v-file-input>
        </v-col>
        <v-col cols="2">
          <v-radio-group v-model="dep_reg" row>
            <v-radio label="Депозиторы" :value="1"></v-radio>
            <v-radio label="Регистраторы" :value="2"></v-radio>
            <v-radio label="Recovery" :value="3"></v-radio>
          </v-radio-group>
        </v-col>

        <v-col cols="2">
          <v-select
            v-model="selectedStatus"
            :items="statuses"
            label="Статус"
            item-text="name"
            item-value="id"
          ></v-select>
        </v-col>
        <v-col cols="2">
          <v-text-field v-model="sum" label="Сумма"></v-text-field>
          <v-radio-group v-model="cp" row>
            <v-radio label="CPL" value="L"></v-radio>
            <v-radio label="CPA" value="A"></v-radio>
          </v-radio-group>
        </v-col>
        <v-col cols="2">
          <v-textarea
            v-model="load_mess"
            label="Сообщение"
            :rules="[(v) => v.length <= 190 || 'Max 190 characters']"
            rows="1"
          ></v-textarea>
        </v-col>
      </v-row>
      <v-progress-linear
        :active="loading"
        indeterminate
        color="purple"
      ></v-progress-linear>
      <v-row v-if="table.length && files">
        <v-col cols="9">
          <v-simple-table id="loadedTable">
            <template v-slot:default>
              <thead>
                <tr>
                  <th v-for="el in table[0].length" :key="el">
                    <v-select
                      :items="[
                        '',
                        'name',
                        'lastname',
                        'email',
                        'tel',
                        'afilyator',
                        'text',
                        'deposit',
                      ]"
                      outlined
                      @change="makeHeader"
                    >
                    </v-select>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, ix) in table" :key="ix">
                  <td v-for="(it, i) in item" :key="i">{{ it }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
        <v-col cols="3">
          <v-card height="100%" class="pa-5">
            Укажите пользователя для лидов
            <v-list>
              <div v-for="office in offices" :key="office.id">
                <p class="title" v-if="office.id > 0">{{ office.name }}</p>
                <v-expansion-panels v-model="akkvalue[office.id]">
                  <v-expansion-panel
                    v-for="item in group.filter(
                      (g) => g.office_id == office.id
                    )"
                    :key="item.group_id"
                  >
                    <v-expansion-panel-header>
                      {{ item.fio }}
                      <div></div>
                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                      <v-col
                        v-for="user in users.filter(function (i) {
                          return i.group_id == item.group_id;
                        })"
                        :key="user.id"
                      >
                        <input
                          type="checkbox"
                          :id="user.id"
                          :value="user.id"
                          v-model="userids"
                        />
                        <label :for="user.id"
                          >{{ user.fio }}
                          <v-badge
                            :content="user.hmlids"
                            :value="user.hmlids"
                            :color="usercolor(user)"
                            overlap
                          >
                            <!-- <v-icon large v-if="user.role_id === 2">
                              mdi-account-group-outline
                            </v-icon>
                            <v-icon large v-else> mdi-account-outline </v-icon> -->
                          </v-badge></label
                        >
                      </v-col>
                    </v-expansion-panel-content>
                  </v-expansion-panel>
                </v-expansion-panels>
              </div>

              <v-btn class="btn ma-3" @click="putSelectedLidsDB"
                >Назначить</v-btn
              >
            </v-list>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import XLSX from "xlsx";
import axios from "axios";
import _ from "lodash";
export default {
  props: ["user"],
  data: () => ({
    loading: false,
    load_mess: "",
    message: "",
    snackbar: false,
    providers: [],
    selectedProvider: 0,
    selectedBaer: "",
    baers: [],
    users: [],
    statuses: [],
    selectedStatus: 0,
    files: null,
    table: [],
    header: [],
    userid: null,
    userids: [],
    related_user: [],
    tab: 0,
    dep_reg: 1,
    sum: 0,
    cp: "L",
    akkvalue: [],
    group: [],
    offices: [],
  }),

  mounted() {
    this.getUsers();
    this.getProviders();
    this.getStatuses();
    this.getOffices();
  },
  watch: {
    selectedProvider: function (newval) {
      if (this.providers && this.providers.length && newval > 0) {
        let baer = this.providers.find((p) => {
          return p.id == newval;
        }).baer;
        this.baers = [];
        if (baer && baer != "") {
          this.baers = baer.split(";");
        }
      }
    },
  },
  methods: {
    getOffices() {
      let self = this;
      self.filterOffices = self.$props.user.office_id;
      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
          // if (self.$props.user.role_id == 1) {
          //   self.offices.unshift({ id: 0, name: "--выбор--" });
          //   self.filterOffices = self.offices[1].id;
          // }
          if (self.$props.user.office_id > 0) {
            self.offices = self.offices.filter(
              (o) => o.id == self.$props.user.office_id
            );
          }
        })
        .catch((error) => console.log(error));
    },
    getheader() {
      setTimeout(() => {
        this.header = document
          .querySelector("#loadedTable table")
          .tHead.innerText.split("\t")
          .map((i) => i.replaceAll(/[\W_]+/g, ""));
      }, 300);
    },
    makeHeader() {
      this.getheader();
    },
    onFileChange(f) {
      if (f == null) return;
      const ftype = [
        "application/vnd.ms-excel",
        "application/vnd-xls",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/xls",
        "application/x-xls",
        "application/vnd.ms-excel",
        "application/msexcel",
        "application/x-msexcel",
        "application/x-ms-excel",
        "application/x-excel",
        "application/x-dos_ms_excel",
        "application/excel",
      ];
      if (ftype.indexOf(f.type) >= 0) {
        this.createInput(f);
      }
    },
    createInput(f) {
      let vm = this;
      var reader = new FileReader();
      reader.onload = function (e) {
        var data = e.target.result,
          fixedData = vm.fixdata(data),
          workbook = XLSX.read(btoa(fixedData), { type: "base64" }),
          firstSheetName = workbook.SheetNames[0],
          worksheet = workbook.Sheets[firstSheetName];
        vm.table = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
      };
      reader.readAsArrayBuffer(f);
    },
    fixdata(data) {
      var o = "",
        l = 0,
        w = 10240;
      for (; l < data.byteLength / w; ++l)
        o += String.fromCharCode.apply(
          null,
          new Uint8Array(data.slice(l * w, l * w + w))
        );
      o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
      return o;
    },
    async newlids(data) {
      const self = this;
      let info = {};
      await axios
        .post("api/Lid/newlids", data)
        .then(function (response) {
          // save to imports db
          //======================
          info.start = response.data.date_start
            .substring(0, 19)
            .replace("T", " ");
          info.end = response.data.date_end.substring(0, 19).replace("T", " ");
        })

        .catch(function (error) {
          console.log(error);
        });
      return info;
    },
    splitToNChunks(array, n) {
      let result = [];
      for (let i = n; i > 0; i--) {
        result.push(array.splice(0, Math.ceil(array.length / i)));
      }
      return result;
    },
    putSelectedLidsDB() {
      let self = this;
      if (self.load_mess == "") {
        self.message = 'Обязательно заполните поле "Сообщение"';
        self.snackbar = true;

        return false;
      }
      let json = {};
      //make json from header and body
      json = this.table.map((_, row) =>
        this.header.reduce(
          (json, key, col) => ({
            ...json,
            [key]: this.table[row][col] ?? "",
          }),
          {}
        )
      );
      //remove empty columns
      json = Object.entries(json).map(
        (e) =>
          (e[1] = Object.fromEntries(
            Object.entries(e[1]).filter((el) => el[0])
          ))
      );

      self.loading = true;
      let send = {};
      send.provider_id = this.selectedProvider;
      if (this.selectedStatus !== 0) {
        send.status_id = this.selectedStatus;
      }
      send.message = self.load_mess;
      send.dep_reg = self.dep_reg;
      if (this.userids.length == 0) {
        self.userids = [self.user.id];
      }
      let n_arr = self.splitToNChunks(json, this.userids.length);
      let info = {};
      let ans_info = {};
      info.start = "";
      info.end = "";

      n_arr.forEach(async (arr, i) => {
        send.user_id = this.userids[i];
        send.data = arr;
        ans_info = await self.newlids(send);

        if (info.start == "") {
          info.start = ans_info.start;
          info.end = ans_info.end;
        } else {
          info.end = ans_info.end;
        }
        if (this.userids.length == i + 1) {
          info.provider_id = self.selectedProvider;
          info.baer = self.selectedBaer ?? "";
          info.user_id = self.user.id;
          info.sum = self.sum;
          info.cp = self.cp;
          info.message = self.load_mess;
          axios
            .post("api/imports/0/0", info)
            .then(function (response) {
              self.selectedProvider = null;
              self.selectedBaer = "";
              self.loading = false;
              json = {};
              send = {};
              self.header = [];
              self.files = null;
              self.table = [];
              self.load_mess = "";
            })
            .catch(function (error) {
              console.log(error);
            });
        }
      });

      //
      // axios
      //   .post("api/Lid/newlids", send)
      //   .then(function (response) {
      //     // self.getUsers();
      //     json = {};
      //     send = {};
      //     self.header = [];
      //     self.files = null;
      //     self.table = [];
      //     // save to imports db
      //     //======================
      //     let info = {};

      //     info.start = response.data.date_start
      //       .substring(0, 19)
      //       .replace("T", " ");
      //     info.end = response.data.date_end.substring(0, 19).replace("T", " ");
      //     info.provider_id = self.selectedProvider;
      //     info.user_id = self.user.id;
      //     info.sum = self.sum;
      //     info.cp = self.cp;
      //     info.message = self.load_mess;
      //     // console.log(info);
      //     return info;
      //   })
      //   .then((res) => {
      //     axios
      //       .post("api/imports/0/0", res)
      //       .then(function (response) {
      //         self.selectedProvider = null;
      //         self.loading = false;
      //       })
      //       .catch(function (error) {
      //         console.log(error);
      //       });
      //   })
      //   .catch(function (error) {
      //     console.log(error);
      //   });
    },
    getProviders() {
      let self = this;
      axios
        .get("/api/provider")
        .then((res) => {
          self.providers = res.data.map(
            ({ name, id, related_users_id, baer }) => ({
              name,
              id,
              related_users_id,
              baer,
            })
          );
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data.map(
            ({ name, id, role_id, fio, hmlids, group_id, office_id }) => ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
            })
          );
          self.loading = false;
          self.group = _.filter(self.users, function (o) {
            return o.group_id == o.id;
          });
        })
        .catch((error) => console.log(error));
    },
    getUsers_del() {
      let self = this;
      this.loading = true;
      axios
        .post("/api/getusers", [])
        .then((res) => {
          self.users = res.data.map(
            ({ name, id, role_id, fio, hmlids, group_id, office_id }) => ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
            })
          );
          self.loading = false;
          self.group = _.filter(self.users, function (o) {
            return o.group_id == o.id;
          });
        })
        .catch((error) => console.log(error));
    },

    usercolor(user) {
      return user.role_id == 2 ? "green" : "blue";
    },
    getStatuses() {
      let self = this;
      axios
        .get("/api/statuses")
        .then((res) => {
          self.statuses = res.data.map(({ uname, name, id, color, order }) => ({
            uname,
            name,
            id,
            color,
            order,
          }));
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>
