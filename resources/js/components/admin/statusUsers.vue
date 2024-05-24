<template>
  <v-row justify="center">
    <v-dialog v-model="dialog">
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          color="primary"
          dark
          v-bind="attrs"
          v-on="on"
          :disabled="$props.o_users.length == 0"
        >
          Отчёт
        </v-btn>
      </template>
      <v-card>
        <v-container fluid>
        <v-row>
          <v-card-title>
            <span class="headline">Показатели статусов</span>
            <v-spacer></v-spacer>
            <v-col cols="2">
              <v-menu
                v-model="menufrom"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="datefrom"
                    label="От"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="datefrom"
                  @input="menufrom = false"
                  locale="ru-ru"
                ></v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="2">
              <v-menu
                v-model="menuto"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-text-field
                    v-model="dateto"
                    label="До"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                  ></v-text-field>
                </template>
                <v-date-picker
                  v-model="dateto"
                  @input="menuto = false"
                  locale="ru-ru"
                ></v-date-picker>
              </v-menu>
            </v-col>
            <v-btn color="primary darken-1" @click="report">
              Сформировать
            </v-btn>
            <v-btn
              color="primary darken-1"
              text
              @click="(dialog = false), (users = [])"
            >
              Закрыть
            </v-btn>
          </v-card-title>
        </v-row>
        <v-row>
          <v-col>
            <v-simple-table>
              <template v-slot:default>
                <tbody>
                  <tr class="item" v-for="(item, ix) in dates" :key="ix" :style="{'background':item.color}">
                    <td  v-for="(i, ix) in item.col" :key="ix">
                      {{ i }}
                    </td>
                  </tr>
                </tbody>
              </template>
            </v-simple-table>
                      <download-csv
            :data="dates"
            delimiter=";"
            :name="'Статусы (' + datefrom.replace('.','-') + ' - '+dateto.replace('.','-')+').csv'"
          >
            <v-btn depressed> Сохранить CSV </v-btn>
            <v-icon> mdi-download-circle </v-icon>
          </download-csv>
          </v-col>
        </v-row>
        </v-container>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import axios from "axios";
import JsonCSV from "vue-json-csv";
export default {
  props: ["o_users"],
  data() {
    return {
      datefrom: new Date((new Date()).setDate((new Date()).getDate() - 7)).toISOString().substr(0, 10),
      dateto: new Date().toISOString().substr(0, 10),
      menuto: false,
      modalto: false,
      menufrom: false,
      modalfrom: false,

      dialog: false,
      users: [],
      dates: [],
    };
  },
  mounted: function () {},
  methods: {
    report() {
      if (this.$props.o_users.length) {

        let self = this;
        let send = {}
        send.users = this.$props.o_users.map((e) => e.id)
        send.datefrom = self.datefrom
        send.dateto = self.dateto
        axios
          .post("/api/status_users",send)
          .then((res) => {
            self.dates = res.data;
          })
          .catch((error) => console.log(error));
      }
    },
  },
    components: {
    downloadCsv: JsonCSV,
  },
};
</script>
<style scoped>
tr.item td:first-child {
    /* display: none; */
    width: 200px;
}
tr.item td:nth-child(2) {
    /* width: 200px; */
}
</style>