<template>
  <div>
    <v-row>
      <v-col>
        <v-progress-linear
          :active="loading"
          :indeterminate="loading"
          color="deep-purple accent-4"
        ></v-progress-linear>
        <!-- <v-card class="pa-5 w-100"> -->
        <v-expansion-panels>
          <v-expansion-panel
            v-for="(item, i) in group"
            :key="i"
            @click="getData()"
          >
            <v-expansion-panel-header>
              <v-row>
                <v-col cols="3">{{ item.fio }}</v-col>
                <v-col cols="3">{{ sumGroup(item.group_id) }}</v-col>
              </v-row>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <!-- :expanded.sync="expanded" -->
              <v-data-table
                :headers="tableHeaders"
                :items="tableData.filter((i) => i.group_id == item.group_id)"
                :single-expand="true"
                item-key="id"
                show-expand
                class="elevation-1"
                hide-default-footer
                disable-pagination
                @click:row="
                  (item, slot) => {
                    slot.expand(!slot.isExpanded);
                    StatusesDay = [];
                    getData(item.id);
                  }
                "
              >
                <template v-slot:item.mbalans="{ item }">
                  {{ item.mbalans }}
                </template>
                <template v-slot:item.balans="{ item }">
                  {{ item.balans }}
                </template>
                <template v-slot:item.setbalans="{ item }">
                  <v-text-field
                    append-icon="mdi-database-plus"
                    @click:append="setBalans(item.id)"
                    :id="'b' + item.id"
                  >
                  </v-text-field>
                </template>

                <template v-slot:item.deleteData="{ item }">
                  <v-btn
                    color="primary"
                    dark
                    @click="
                      clearUser = item.id;
                      dialog = true;
                    "
                  >
                    Стереть
                  </v-btn>
                </template>
                <template v-slot:expanded-item="{ headers }">
                  <td :colspan="headers.length">
                    <div>
                      <template v-for="(i, ix) in StatusesDay">
                        <div class="status_wrp" :key="ix">
                          <span :style="{ background: i[1][0].color }">{{
                            i[0]
                          }}</span
                          ><b>{{ i[1].length }}</b>
                        </div>
                      </template>
                    </div>
                  </td>
                </template>
              </v-data-table>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
        <!-- </v-card> -->
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-card>
          <v-card-title primary-title>
            <v-row>
              <v-col cols="3"></v-col>
              <v-col cols="3">{{ sumAll() }}</v-col>
            </v-row>
          </v-card-title>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog v-model="dialog" width="500">
      <v-card>
        <v-card-title class="text-h5 grey lighten-2">
          Удалить данные пользователя?
        </v-card-title>
        <v-card-text>
          Балансы, телефонные звонки будут удалены без возможности
          восстановления!
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @click="
              dialog = false;
              delDataUser();
            "
          >
            Удалить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>


<script>
import axios from "axios";
import _ from "lodash";
export default {
  data: () => ({
    dialog: false,
    expanded: [],
    tableHeaders: [
      { text: "ФИО", value: "fio" },
      { text: "Кол-во лидов", value: "hmlids" },
      { text: "Кол-во наборов", value: "hmcalls" },
      { text: "Общее время", value: "alltime" },
      { text: "Баланс месяц", value: "mbalans" },
      { text: "Баланс день", value: "balans" },
      { text: "Установка баланса", value: "setbalans" },
      { text: "Очистить", value: "deleteData" },
    ],
    tableData: [],
    users: [],
    StatusesDay: [],
    userid: 0,
    clearUser: 0,
    loading: false,
    user_fio: "",
    dialog: false,
  }),
  mounted: function () {
    this.getData();
  },
  computed: {},
  methods: {
    sumAll() {
      return _.sumBy(this.tableData, function (i) {
        return parseInt(i.balans || 0);
      });
    },
    sumGroup(group_id) {
      return _.sumBy(
        _.filter(this.tableData, function (i) {
          return i.group_id == group_id && i.balans > 0;
        }),
        function (i) {
          return parseInt(i.balans);
        }
      );
    },
    delDataUser() {
      let self = this;
      axios
        .delete("/api/delDataUser/" + self.clearUser)
        .then((res) => {
          // self.getData(self.userid);
        })
        .catch((error) => console.log(error));
    },
    getData(user_id) {
      let self = this;
      self.loading = true;
      user_id = user_id || 0;
      axios
        .get("/api/getDataDay/" + user_id)
        .then((res) => {
          if (user_id > 0) {
            self.StatusesDay = Object.entries(_.groupBy(res.data, "name"));
          } else {
            self.tableData = res.data;
            self.tableData = self.tableData.map(function (i) {
              let t = (i.alltime / 60 / 60).toFixed(2).toString().split(".");
              i.alltime = t[0] + " час. " + t[1] + " мин.";
              return i;
            });
          }
          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
    setBalans(user_id) {
      let self = this;
      let data = {};
      let el = document.getElementById("b" + user_id);
      let balans = el.value || 0;
      el.value = "";
      el.change;
      data.balans = balans;
      data.id = user_id;
      axios
        .post("/api/setBalans", data)
        .then((res) => {
          let elm = self.tableData.find((i) => i.id == user_id);
          elm.balans = (
            parseInt(elm.balans || 0) + parseInt(balans)
          ).toString();
        })
        .catch((error) => console.log(error));
    },
  },
  computed: {
    group() {
      return this.tableData.filter((i) => i.group_id == i.id);
    },
  },
};
</script>

<style scoped>
.status_wrp span {
  padding: 5px;
  word-break: break-all;
}

.status_wrp b {
  padding: 0 8px;
}

.status_wrp {
  margin-right: 15px;
  border: 1px solid grey;
  padding: 3px 0;
  margin-bottom: 15px;
  display: inline-block;
}
</style>
