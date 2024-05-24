<template>
  <div>
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
                    getCalls();
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
                    getCalls();
                  "
                ></v-date-picker>
              </v-menu>
            </v-col>

            <v-btn @click="cleardate" small text><v-icon>close</v-icon></v-btn>
          </v-row>
        </div>
      </v-col>
      <v-col v-if="$props.user.role_id == 1 && $props.user.office_id == 0">
        <p>Фильтр office</p>
        <v-select
          v-model="filterOffices"
          :items="offices"
          item-text="name"
          item-value="id"
          outlined
          rounded
          @change="getCalls"
        >
          <!--
            <template v-slot:selection="{ item, index }">
              <v-chip v-if="index === 0">
                <span>{{ item.name }}</span><span v-if="index === 1" class="grey--text text-caption">
                (+{{ filterOffices.length - 1 }} )
              </span>
              </v-chip>
            </template>-->
        </v-select>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <div class="my-3">Поиск пользователей</div>
        <v-autocomplete
          v-model="selectedUser"
          :items="calls"
          label="Выбор"
          item-text="name"
          item-value="name"
          :return-object="true"
          append-icon="mdi-close"
          outlined
          rounded
          @click:append="clearuser()"
        ></v-autocomplete>
        <v-expansion-panels v-model="akkvalue">
          <v-expansion-panel v-for="(item, i) in test" :key="i">
            <v-expansion-panel-header>
              {{ item[0] }}
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-row>
                <v-col cols="12">
                  <v-data-table
                    class="tablecalls"
                    :headers="headers"
                    :search="selectedUser.name"
                    item-key="id"
                    :items="item[1]"
                    :hide-default-footer="true"
                    :items-per-page="-1"
                    :loading="loading"
                    loading-text="Загружаю... Ожидайте"
                  >
                  </v-data-table>
                </v-col>
              </v-row>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
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
    calls: [],
    headers: [
      { text: "Менеджер", value: "name" },
      { text: "Всего звонков", value: "cnt" },
      { text: "Уникальные", value: "utel" },
      { text: "Ответил", value: "good" },
      { text: "Без ответа", value: "bad" },
      { text: "Общее время работы", value: "work" },
      { text: "Общее время разговора", value: "dur" },
      { text: "Среднее в режиме разговора", value: "avg" },
      { text: "Эфективные звонки (более 2 минут)", value: "mr2" },
      //{ text: "Пауза", value: "pausa" },
      //{ text: "Ср пауза", value: "spausa" },
    ],
    selected: [],
    dateFrom: false,
    dateTo: false,
    dateProps: { locale: "ru-RU", format: "24hr" },
    datetimeFrom: new Date(new Date().setDate(new Date().getDate() - 1))
      .toISOString()
      .substring(0, 10),
    datetimeTo: new Date(new Date().setDate(new Date().getDate() - 1))
      .toISOString()
      .substring(0, 10),
    offices: [],
    filterOffices: 1,
    loading: false,
    group: [],
    users: [],
    selectedUser: {},
    akkvalue: [],
    test: [],
  }),
  mounted: function () {
    this.getOffices();
  },
  watch: {
    selectedUser(user) {
      this.akkvalue = _.findIndex(this.test, (el) => el[0] == user.grp);
    },
  },
  methods: {
    clearuser() {
      this.selectedUser = {};
    },
    getOffices() {
      let self = this;
      if (self.$props.user.role_id == 1 && self.$props.user.office_id == 0) {
        axios
          .get("/api/getOffices")
          .then((res) => {
            self.offices = res.data;
            self.offices.unshift({ id: 0, name: "--выбор--" });
            self.filterOffices = 0;

            self.getCalls();
          })
          .catch((error) => console.log(error));
      } else {
        self.filterOffices = self.$props.user.office_id;
        self.getCalls();
      }
    },
    cleardate() {
      this.datetimeFrom = new Date(new Date().setDate(new Date().getDate() - 1))
        .toISOString()
        .substring(0, 10);
      this.datetimeTo = new Date(new Date().setDate(new Date().getDate() - 1))
        .toISOString()
        .substring(0, 10);
    },
    getCalls() {
      let self = this;
      let data = {};
      self.loading = true;
      data.dateFrom = this.datetimeFrom;
      data.dateTo = this.datetimeTo;
      data.office_id = self.filterOffices;
      if (self.$props.user.role_id == 2) {
        data.user_id_group = self.$props.user.group_id;
      }

      axios
        .post("/api/getCalls", data)
        .then((res) => {
          self.calls = res.data;
          self.group = _.groupBy(self.calls, "grp");
          self.test = Array.from(Object.keys(self.group), (k) => [
            `${k}`,
            self.group[k],
          ]);

          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>
