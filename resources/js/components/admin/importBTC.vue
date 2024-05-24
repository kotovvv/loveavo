<template>
  <div>
    <v-snackbar v-model="snackbar" top right timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <v-container>
      <v-row class="mb-5 align-center">
        <v-col cols="2">
          <v-select
            v-model="selectOffice"
            :items="offices"
            item-text="name"
            item-value="id"
            outlined
            rounded
          >
          </v-select>
        </v-col>
        <v-col cols="2">
          <v-file-input
            v-model="files"
            ref="fileupload"
            label="загрузить файл ВТС CSV"
            show-size
            truncate-length="24"
            @change="onFileChange"
          ></v-file-input>
        </v-col>

        <v-col cols="2"
          ><h2>Free: {{ count_free }}</h2>
        </v-col>
        <v-col cols="6">
          <v-row>
            <v-col>
              <v-row class="px-4">
                <v-col><p>С Дата</p></v-col>
                <v-col><p>По Дата</p></v-col>
              </v-row>

              <div class="status_wrp wrp_date px-3">
                <v-row align="center">
                  <v-col>
                    <v-menu
                      v-model="dateFrom"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="datetimeFrom"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        locale="ru-ru"
                        v-model="datetimeFrom"
                        @input="
                          dateFrom = false;
                          getBTCsOnDate();
                        "
                      ></v-date-picker>
                    </v-menu>
                  </v-col>
                  <v-col>
                    <v-menu
                      v-model="dateTo"
                      :close-on-content-click="false"
                      :nudge-right="40"
                      transition="scale-transition"
                      offset-y
                      min-width="auto"
                    >
                      <template v-slot:activator="{ on, attrs }">
                        <v-text-field
                          v-model="datetimeTo"
                          readonly
                          v-bind="attrs"
                          v-on="on"
                        ></v-text-field>
                      </template>
                      <v-date-picker
                        locale="ru-ru"
                        v-model="datetimeTo"
                        @input="
                          dateTo = false;
                          getBTCsOnDate();
                        "
                      ></v-date-picker>
                    </v-menu>
                  </v-col>
                </v-row>
              </div>
            </v-col>
            <v-col>
              <p>Фильтр office</p>
              <v-select
                v-model="filterOffices"
                :items="offices"
                item-text="name"
                item-value="id"
                outlined
                rounded
                multiple
                @change="set_count_free"
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
          </v-row>
        </v-col>
      </v-row>
      <v-progress-linear
        :active="loading"
        :indeterminate="loading"
        color="deep-purple accent-4"
      ></v-progress-linear>
    </v-container>
    <v-row>
      <v-col cols="12">
        <v-data-table
          id="tabtc"
          :headers="headers"
          item-key="id"
          :items="filteredItems"
          :items-per-page="50"
          :disable-pagination="true"
          :loading="loading"
          loading-text="Загружаю... Ожидайте"
        >
          <template
            v-slot:top="{ pagination, options, updateOptions }"
            :footer-props="{
              'items-per-page-options': [50, 10, 100, 250, 500, -1],
              'items-per-page-text': '',
            }"
          >
            <v-row>
              <!-- <v-spacer></v-spacer> -->
              <v-col cols="3" class="mt-3">
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
      </v-col>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
export default {
  data() {
    return {
      snackbar: false,
      message: "",
      files: [],
      parse_csv: "",
      loading: false,
      headers: [
        { text: "Лид", value: "lid_id" },
        { text: "Адрес", value: "address" },
        { text: "Сумма", value: "summ" },
        { text: "TRX", value: "trx_count" },
        { text: "Дата", value: "date_time" },
        { text: "Менеджер", value: "fio" },
      ],
      modal: false,
      dateFrom: false,
      dateTo: false,

      dateProps: { locale: "ru-RU", format: "24hr" },
      datetimeFrom: new Date(new Date().setDate(new Date().getDate() - 14))
        .toISOString()
        .substring(0, 10),
      datetimeTo: new Date().toISOString().substring(0, 10),
      btc: [],
      free: [],
      offices: [],
      filterOffices: [],
      selectOffice: "",
      count_free: 0,
    };
  },
  mounted() {
    this.getOffices();
    this.getBTCsOnDate();
  },
  computed: {
    filteredItems() {
      return this.btc.filter((i) => {
        return (
          !this.filterOffices.length || this.filterOffices.includes(i.office_id)
        );
      });
    },
  },
  methods: {
    set_count_free() {
      if (this.filterOffices.length == 0) {
        this.count_free = this.free.reduce((ak, el) => ak + el.count, 0);
      } else {
        this.count_free = this.free.reduce((ak, el) => {
          if (this.filterOffices.includes(el.office_id)) {
            return ak + el.count;
          } else {
            return ak + 0;
          }
        }, 0);
      }
    },
    getOffices() {
      let self = this;

      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
          self.filterOffices.push(self.offices[0].id);
          self.selectOffice = self.offices[0].id;
        })
        .catch((error) => console.log(error));
    },
    putBTC() {
      const self = this;
      this.loading = true;
      let send = {};
      send.office_id = self.selectOffice;
      send.data = this.parse_csv;
      axios
        .post("/api/putBTC", send)
        .then(function (response) {
          self.message =
            "Импорт из файла - " +
            self.files.name +
            "<br>Новых записей " +
            response.data;
          self.loading = false;
          self.files = [];
          self.snackbar = true;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getLocalDateTime(DateTime) {
      return new Date(
        new Date(DateTime).getTime() -
          new Date(DateTime).getTimezoneOffset() * 60 * 1000
      )
        .toJSON()
        .slice(0, 16)
        .replace("T", " ");
    },
    getBTCsOnDate() {
      let self = this;
      let data = {};
      self.loading = true;
      data.datefrom = this.getLocalDateTime(self.datetimeFrom);
      data.dateto = this.getLocalDateTime(self.datetimeTo);

      axios
        .post("/api/getBTCsOnDate", data)
        .then(function (response) {
          self.btc = response.data.list;
          self.free = response.data.free;
          self.loading = false;
          self.set_count_free();
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    onFileChange(f) {
      if (f == null) return;
      const ftype = [
        "text/comma-separated-values",
        "text/csv",
        "application/csv",
        "application/excel",
        "application/vnd.ms-excel",
        "application/vnd.msexcel",
        "text/anytext",
        "text/plain",
      ];
      if (ftype.indexOf(f.type) >= 0) {
        this.createInput(f);
      } else {
        this.files = [];
      }
    },
    csvJSON(csv) {
      var vm = this;
      var lines = csv.split("\n");
      var result = [];
      var headers = lines[0].split(";");
      headers = ["address"]; //, "summ", "trx_count"

      lines.map(function (line, indexLine) {
        if (indexLine < 1) return; // Jump header line

        var obj = {};
        line = line.trim();
        var currentline = line.split(";");

        headers.map(function (header, indexHeader) {
          obj[header] = currentline[indexHeader];
        });

        result.push(obj);
      });

      result.pop(); // remove the last item because undefined values

      return result; // JavaScript object
    },
    createInput(file) {
      let promise = new Promise((resolve, reject) => {
        var reader = new FileReader();
        var vm = this;
        reader.onload = (e) => {
          resolve((vm.fileinput = reader.result));
        };
        reader.readAsText(file);
      });

      promise.then(
        (result) => {
          let vm = this;
          vm.parse_csv = vm.csvJSON(this.fileinput);
          vm.putBTC();
        },
        (error) => {
          /* handle an error */
          console.log(error);
        }
      );
    },
  },
};
</script>
