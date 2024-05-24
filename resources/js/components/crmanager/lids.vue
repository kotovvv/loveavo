<template>
  <div>
    <v-container fluid>
      <v-row>
        <v-col>
          Фильтр по статусам
          <v-select
            v-model="filterStatus"
            :items="statuses"
            item-text="name"
            item-value="id"
            outlined
            rounded
          >
            <template v-slot:selection="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
            <template v-slot:item="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
          </v-select>
          <v-btn
            v-if="
              filterStatus &&
              $props.user.role_id == 1 &&
              filteredItems.length > 0
            "
          >
            <!-- <v-icon small @click="deleteItem()"> mdi-delete </v-icon> -->
          </v-btn>
        </v-col>
        <v-col>
          Глобальный статус
          <v-select
            v-model="filterGStatus"
            :items="statuses"
            item-text="name"
            item-value="id"
            outlined
            rounded
          >
            <template v-slot:selection="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
            <template v-slot:item="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
          </v-select>
        </v-col>

        <v-col>
          Фильтр по поставщикам
          <v-select
            v-model="filterProviders"
            :items="providers"
            item-text="name"
            item-value="id"
            outlined
            rounded
          ></v-select>
        </v-col>
        <v-col v-if="$props.user.role_id == 1 && $props.user.office_id == 0">
          Фильтр office
          <v-select
            v-model="filterOffices"
            :items="offices"
            item-text="name"
            item-value="id"
            outlined
            rounded
            multiple
          >
            <template v-slot:selection="{ item, index }">
              <v-chip v-if="index === 0">
                <span>{{ item.name }}</span>
              </v-chip>
              <span v-if="index === 1" class="grey--text text-caption">
                (+{{ filterOffices.length - 1 }} )
              </span>
            </template>
          </v-select>
        </v-col>

        <v-col>
          Глобальный поиск
          <v-text-field
            v-model="searchAll"
            append-icon="mdi-magnify"
            @click:append="searchInDB"
            single-line
            hide-details
            class="border px-2"
          ></v-text-field>
        </v-col>
        <v-col>
          Телефон
          <v-text-field
            v-model.lazy.trim="filtertel"
            append-icon="mdi-phone"
            class="border px-2"
          ></v-text-field>
        </v-col>

        <v-col>
          Назначение статусов
          <v-select
            v-model="selectedStatus"
            :items="filterstatuses"
            item-text="name"
            item-value="id"
            outlined
            rounded
          >
            <template v-slot:selection="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
            <template v-slot:item="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
          </v-select>
          <v-btn
            v-if="selectedStatus && selected.length"
            color="dark primary"
            x-large
            @click="changeStatus"
          >
            Сменить статусы
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
    <v-row>
      <v-col cols="12">
        <v-progress-linear
          :active="loading"
          indeterminate
          color="purple"
        ></v-progress-linear>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="9">
        <div class="border pa-4">
          <v-data-table
            id="tablids"
            v-model.lazy.trim="selected"
            :headers="headers"
            :search="search"
            :single-select="false"
            item-key="id"
            show-select
            @click:row="clickrow"
            :items="filteredItems"
            ref="datatable"
            :footer-props="{
              'items-per-page-options': [50, 10, 100, 250, 500, -1],
              'items-per-page-text': '',
            }"
          >
            <template
              v-slot:top="{ pagination, options, updateOptions }"
              :footer-props="{
                'items-per-page-options': [50, 10, 100, 250, 500, -1],
                'items-per-page-text': '',
              }"
            >
              <v-row>
                <v-col cols="4">
                  <v-text-field
                    v-model="search"
                    append-icon="mdi-magnify"
                    label="Поиск"
                    single-line
                    hide-details
                    class="ml-3 border px-2"
                  ></v-text-field>
                </v-col>
                <!-- v-if="telsDuplicates.length > 0" -->
                <v-col cols="2">
                  <v-card-title>
                    <v-checkbox
                      v-model="showDuplicates"
                      class="mt-0"
                      @click="getDuplicates"
                    >
                      <template v-slot:label>
                        <v-icon small> mdi-phone </v-icon>
                        <v-icon small> mdi-phone </v-icon>
                      </template>
                      ></v-checkbox
                    >
                  </v-card-title>
                </v-col>
                <v-col cols="6">
                  <v-data-footer
                    :pagination="pagination"
                    :options="options"
                    @update:options="updateOptions"
                    :items-per-page-options="[50, 10, 100, 250, 500, -1]"
                    :items-per-page-text="''"
                  />
                </v-col>
              </v-row>
            </template>
          </v-data-table>
        </div>
      </v-col>
      <v-col cols="3" class="mt-4 wrp_users">
        <div class="row">
          <v-card rounded class="rounded-xl pa-5 w-100">
            Укажите пользователя
            <div class="scroll-y">
              <v-list>
                <v-radio-group
                  @change="changeLidsUser"
                  ref="radiogroup"
                  v-model="userid"
                  v-bind="users"
                  id="usersradiogroup"
                >
                  <v-expansion-panels ref="akk" v-model="akkvalue">
                    <v-expansion-panel v-for="(item, i) in group" :key="i">
                      <v-expansion-panel-header>
                        <img
                          class="v-expansion-panel-header__icon mr-1"
                          height="60"
                          width="60"
                          :src="'/storage/' + item.pic"
                          v-if="item.pic"
                        />

                        {{ item.fio }}
                        <div></div>
                      </v-expansion-panel-header>
                      <v-expansion-panel-content>
                        <v-row
                          v-for="user in users.filter(function (i) {
                            return i.group_id == item.group_id;
                          })"
                          :key="user.id"
                        >
                          <v-radio
                            :label="user.fio"
                            :value="user.id"
                            :disabled="disableuser == user.id"
                          >
                          </v-radio>

                          <v-btn
                            class="ml-3"
                            small
                            :color="usercolor(user)"
                            @click="
                              disableuser = user.id;
                              getLids(user.id);
                            "
                            :value="user.hmlids"
                            :disabled="disableuser == user.id"
                            >{{ user.hmlids }}</v-btn
                          >
                          <v-chip v-if="user.statnew" label small>
                            {{ user.statnew }}
                          </v-chip>
                        </v-row>
                      </v-expansion-panel-content>
                    </v-expansion-panel>
                  </v-expansion-panels>
                </v-radio-group>
              </v-list>
            </div>
          </v-card>
          <v-card class="border pa-5 mt-1 w-100">
            <div class="tel">Тел: {{ clickedItemTel }}</div>

            <div class="mt-4">
              <div
                v-for="(item, i) in clickedItemStatuses"
                :key="i"
                class="blk_statuses"
              >
                <span class="status_wrp">
                  <b
                    :style="{
                      background: item.color,
                      outline: '#999 solid 1px',
                    }"
                  ></b>
                  <span>{{ item.name }}</span> </span
                ><span>{{ item.uname }}</span> <span>{{ item.cdate }}</span>
              </div>
            </div>
          </v-card>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
import _ from "lodash";

export default {
  props: ["user"],
  data: () => ({
    akkvalue: null,
    datetime: "",
    userid: null,
    users: [],
    disableuser: 0,
    statuses: [],
    filterstatuses: [],
    selectedStatus: 0,
    filterStatus: 0,
    filterGStatus: 0,
    filterProviders: 0,
    selected: [],
    lids: [],
    search: "",
    searchAll: "",
    filtertel: "",
    headers: [
      { text: "Имя", value: "name" },
      { text: "Email", value: "email" },
      { text: "Телефон.", align: "start", value: "tel" },
      { text: "Афилятор", value: "afilyator" },
      { text: "Поставщик", value: "provider" },
      { text: "Менеджер", value: "user" },
      { text: "Создан", value: "date_created" },
      { text: "Статус", value: "status" },
      { text: "Сообщение", value: "text" },
      { text: "Звонков", value: "qtytel" },
      { text: "ПЕРЕЗВОН", value: "ontime" },
    ],
    parse_header: [],
    sortOrders: {},
    sortKey: "tel",
    providers: [],
    showDuplicates: false,
    telsDuplicates: [],
    clickedItemStatuses: [],
    clickedItemTel: "",
    offices: [],
    filterOffices: [],
    loading: false,
  }),
  mounted: function () {
    this.getProviders();
    this.getUsers();
    this.getStatuses();
    this.getOffices();
  },
  watch: {
    filterGStatus: function (newval, oldval) {
      if (newval == 0) {
        this.getLids(this.$props.user.id);
      } else {
        this.getStatusLids(newval);
      }
    },
  },
  computed: {
    group() {
      // return _.uniqBy(this.users, "group_id");
      return _.filter(this.users, function (o) {
        return o.group_id == o.id;
      });
    },
    filteredItems() {
      // if (this.showDuplicates && this.telsDuplicates.length > 0)
      //   return this.telsDuplicates;
      let reg = new RegExp("^" + this.filtertel);
      return this.lids.filter((i) => {
        return (
          (!this.filterStatus || i.status_id == this.filterStatus) &&
          (!this.filterProviders || i.provider_id == this.filterProviders) &&
          (!this.filtertel || reg.test(i.tel)) &&
          (!this.showDuplicates || this.telsDuplicates.includes(i.id)) &&
          (!this.filterOffices || this.filterOffices.includes(i.office_id))
        );
      });
    },
  },
  methods: {
    getOffices() {
      let self = this;
      if (self.$props.user.role_id == 1 && self.$props.user.office_id == 0) {
        axios
          .get("/api/getOffices")
          .then((res) => {
            self.offices = res.data;
            self.filterOffices.push(self.offices[0].id);
          })
          .catch((error) => console.log(error));
      } else {
        self.filterOffices.push(self.$props.user.office_id);
      }
    },
    getDuplicates() {
      this.telsDuplicates = this.lids
        .filter(this.duplicatesOnly)
        .map((f) => f.id);
    },
    duplicatesOnly(v1, i1, self) {
      let ndx = self.findIndex(function (v2, i2) {
        // make sure not looking at the same object (using index to verify)
        // use JSON.stringify for object comparison
        return i1 != i2 && v1.tel == v2.tel;
      });
      return i1 != ndx && ndx != -1;
    },
    searchInDB() {
      let self = this;
      self.loading = true;
      const send = {};

      send.group_id = self.$props.user.group_id;
      send.role_id = self.$props.user.role_id;
      send.search = self.searchAll;
      axios
        .post("api/Lid/searchlids", send)
        .then((res) => {
          self.loading = false;
          self.lids = Object.entries(res.data).map((e) => e[1]);

          self.lids.map(function (e) {
            e.user = self.users.find((u) => u.id == e.user_id).fio;
            e.date_created = e.created_at.substring(0, 10);
            if (self.providers.find((p) => p.id == e.provider_id)) {
              e.provider = self.providers.find(
                (p) => p.id == e.provider_id
              ).name;
            }
            if (e.status_id)
              e.status = self.statuses.find((s) => s.id == e.status_id).name;
          });
          self.disableuser = 0;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    deleteItem() {
      const self = this;
      let ids = [];
      this.filteredItems.forEach(function (el) {
        ids.push(el.id);
        self.lids.splice(
          self.lids.indexOf(self.lids.find((l) => l.id == el.id)),
          1
        );
      });

      axios
        .post("api/Lid/deletelids", ids)
        .then(function (response) {
          self.getUsers();
          // self.getLids(send.user_id);
          self.filterStatus = 0;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    changeLidsUser() {
      const self = this;
      let cur_user_id = this.disableuser;
      let send = {};
      send.user_id = this.userid;
      send.data = [];
      if (this.selectedStatus !== 0) {
        send.status_id = this.selectedStatus;
      }
      if (this.selected.length > 0 && this.$refs.datatable.items.length > 0) {
        send.data = this.selected || this.lids;
      } else {
        this.userid = null;
        return false;
      }
      if (self.$props.user.role_id == 2) {
        //CallBack user not change
        send.data = send.data.filter((f) => f.status_id != 9);
      }
      axios
        .post("api/Lid/changelidsuser", send)
        .then(function (response) {
          self.search = "";
          self.filtertel = "";
          self.userid = null;
          self.$refs.radiogroup.lazyValue = null;
          self.selected = [];
          self.filterStatus = 0;
          self.getUsers();
          self.getLids(cur_user_id);
        })
        .catch(function (error) {
          console.log(error);
        });
      self.searchAll = "";
    },
    putSelectedLidsDB() {
      const self = this;
      let send = {};
      send.user_id = this.userid;

      if (this.selectedStatus !== 0) {
        send.status_id = this.selectedStatus;
      }
      if (this.selected.length > 0 && this.$refs.datatable.items.length > 0) {
        send.data = this.selected;
      } else if (
        (this.search !== "" || this.filtertel !== "") &&
        this.$refs.datatable.$children[0].filteredItems.length > 0
      ) {
        send.data = this.$refs.datatable.$children[0].filteredItems;
        axios
          .post("api/Lid/newlids", send)
          .then(function (response) {
            self.getUsers();
            self.search = "";
            self.filtertel = "";
          })
          .catch(function (error) {
            console.log(error);
          });
      }
      if (this.lids.length == 0) {
        this.files = [];
      }
      this.userid = null;
      this.$refs.radiogroup.lazyValue = null;
      this.getUsers();
    },
    usercolor(user) {
      return user.role_id == 2 ? "green" : "blue";
    },

    clickrow(value) {
      // console.log(value.id,value.tel);
      let self = this;
      this.clickedItemTel = value.tel;
      this.clickedItemStatuses = [];
      axios
        .get("/api/StasusesOfId/" + value.id)
        .then((res) => {
          self.clickedItemStatuses = res.data;
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      self.loading = true;
      // let get = self.$props.user.role_id == 1 ? "/api/users" : "/api/getusers";
      let get = "/api/getusers";
      axios
        .get(get)
        .then((res) => {
          self.loading = false;
          self.users = res.data.map(
            ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              order,
              statnew,
              pic,
            }) => ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              order,
              statnew,
              pic,
            })
          );
          // self.users.sort(function (a, b) {
          //   if (a.order > b.order) {
          //     return 1;
          //   }
          //   if (a.order < b.order) {
          //     return -1;
          //   }
          //   return 0;
          // });
          if (self.$props.user.role_id != 1) {
            self.users = self.users.filter(
              (f) => f.group_id == self.$props.user.group_id
            );
          }
        })
        .catch((error) => console.log(error));
    },

    getStatuses() {
      let self = this;
      self.loading = true;
      axios
        .get("/api/statuses")
        .then((res) => {
          self.loading = false;
          self.statuses = res.data.map(({ uname, name, id, color }) => ({
            uname,
            name,
            id,
            color,
          }));
          self.statuses.unshift({ name: "Default", id: 0 });
          self.getLids(self.$props.user.id);
          self.filterstatuses = self.statuses.filter((e) => e.id != 8);
        })
        .catch((error) => console.log(error));
    },

    getStatusLids(id) {
      let self = this;
      self.loading = true;
      self.filterStatus = 0;
      self.search = "";
      self.filtertel = "";
      const send = {};
      send.group_id = self.$props.user.group_id;
      send.role_id = self.$props.user.role_id;
      send.id = id;
      axios
        .post("/api/statuslids", send)
        .then((res) => {
          self.loading = false;
          self.lids = Object.entries(res.data).map((e) => e[1]);

          self.lids.map(function (e) {
            if (self.users.find((u) => u.id == e.user_id)) {
              e.user = self.users.find((u) => u.id == e.user_id).fio;
            }
            e.date_created = e.created_at.substring(0, 10);
            if (self.providers.find((p) => p.id == e.provider_id)) {
              e.provider = self.providers.find(
                (p) => p.id == e.provider_id
              ).name;
            }
            if (self.statuses.find((s) => s.id == e.status_id))
              e.status = self.statuses.find((s) => s.id == e.status_id).name;
          });
          self.searchAll = "";
          // if (localStorage.filterStatus1) {
          //   self.filterStatus = parseInt(localStorage.filterStatus1);
          // }
          // self.getDuplicates();
        })
        .catch((error) => console.log(error));
    },
    getLids(id) {
      let self = this;
      self.filterStatus = 0;
      self.search = "";
      self.filtertel = "";
      self.disableuser = id;
      axios
        .get("/api/userlids/" + id)
        .then((res) => {
          // console.log(res.data);
          self.lids = Object.entries(res.data).map((e) => e[1]);

          self.lids.map(function (e) {
            if (self.users.find((u) => u.id == e.user_id)) {
              e.user = self.users.find((u) => u.id == e.user_id).fio;
            }
            e.date_created = e.created_at.substring(0, 10);
            if (self.providers.find((p) => p.id == e.provider_id)) {
              e.provider = self.providers.find(
                (p) => p.id == e.provider_id
              ).name;
            }
            if (self.statuses.find((s) => s.id == e.status_id))
              e.status = self.statuses.find((s) => s.id == e.status_id).name;
          });
          self.searchAll = "";
          // if (localStorage.filterStatus) {
          //   self.filterStatus = parseInt(localStorage.filterStatus);
          // }
          // self.getDuplicates();
        })
        .catch((error) => console.log(error));
    },
    changeStatus() {
      const self = this;
      let send = {};
      if (this.selected.length && this.selectedStatus) {
        this.selected.map(function (e) {
          e.status_id = self.selectedStatus;
          e.status = self.statuses.find((s) => s.id == e.status_id).name;
        });
        send.data = this.selected.map((e) => e);
        this.changeLids(send);
      }
    },
    changeLids(send) {
      const self = this;
      axios
        .post("api/Lid/updatelids", send)
        .then(function (response) {
          self.afterUpdateLids();
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    afterUpdateLids() {
      const self = this;
      self.selected = [];
      // self.selectedStatus = 0;
      self.getLids(self.disableuser);
    },
    getProviders() {
      let self = this;
      axios
        .get("/api/provider")
        .then((res) => {
          self.providers = res.data.map(({ name, id }) => ({ name, id }));
          self.providers.unshift({ name: "выбор", id: 0 });
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>

<style scoped>
.blk_statuses {
  display: flex;
  grid-gap: 10px;
}
.blk_statuses .status_wrp {
  display: inline-flex;
}
.v-card__text.scroll-y {
  overflow-y: auto;
  height: 60vh;
}
.tel:hover {
  cursor: url(/img/phone-forward.svg) 10 10, none;
  text-decoration: none;
}
.tel {
  display: block;
  color: #000;
}
#maintable.v-data-table >>> tr {
  outline: 2px solid transparent;
}

#maintable.v-data-table >>> tr:hover,
#maintable.v-data-table >>> tr.v-data-table__selected {
  /* border: 2px solid #000; */
  cursor: pointer;
}
#maintable.v-data-table >>> tr.v-data-table__selected {
  border-bottom: transparent !important;
}
#maintable.v-data-table >>> tr.v-data-table__expanded tr:hover {
  border: none;
}
#maintable >>> .text-start {
  padding: 0 !important;
}
.blackborder {
  /* border: 2px solid #000; */
  border-top: transparent !important;
}
.wrp__statuses {
  display: grid;
  grid-template-columns: repeat(4, auto);
}
.wrp__statuses label {
  border: 3px solid transparent;
}
.wrp__statuses input:checked + label {
  border: 3px solid #7620df;
  border-radius: 30px;
}
.status_wrp span {
  padding: 5px;
  white-space: nowrap;
  overflow: hidden;
  max-width: 85px;
}
</style>
