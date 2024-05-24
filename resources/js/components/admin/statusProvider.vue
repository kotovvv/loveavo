<template>
  <v-row justify="center">
    <v-dialog v-model="dialog">
      <template v-slot:activator="{ on, attrs }">
        <v-btn color="outlined" v-bind="attrs" v-on="on"> Отчёт </v-btn>
      </template>
      <v-card>
        <v-card-title>
          <span class="headline">Поставщик: {{ provider.name }} </span>
          <span v-if="hm">&nbsp;Лидов ({{hm}})</span>
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
          <v-btn color="primary darken-1" @click="report"> Сформировать </v-btn>
          <v-btn color="primary darken-1" text @click="dialog = false">
            Закрыть
          </v-btn>
        </v-card-title>
        <!-- <v-card-subtitle> Лидов в системе: {{ hm }} </v-card-subtitle> -->

        <v-col>
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Дата</th>
                  <th class="text-left">Показатели</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item,i) in statuses" :key="i">
                  <td>{{ item.date }}</td>
                  <td  v-for="(it, ix) in item.statuses" :key="ix" :style="{'background':it.color}">
                      <small>{{ it.name }}</small> {{ it.n }}
                    </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
          <download-csv
            :data="statuses"
            delimiter=";"
            :name="provider.name + '(' + hm + ').csv'"
          >
            <v-btn depressed> Сохранить CSV </v-btn>
            <v-icon> mdi-download-circle </v-icon>
          </download-csv>
        </v-col>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import axios from "axios";
import JsonCSV from "vue-json-csv";
export default {
  props: ["provider"],
  data() {
    return {
            datefrom: new Date((new Date()).setDate((new Date()).getDate() - 7)).toISOString().substr(0, 10),
      dateto: new Date().toISOString().substr(0, 10),
      menuto: false,
      modalto: false,
      menufrom: false,
      modalfrom: false,
      dialog: false,
      hm: 0,
      statuses: [],
    };
  },
  methods: {
    report() {
      let self = this;
      self.statuses =[];
let send = {};
send.provider_id = self.provider.id;
        send.datefrom = self.datefrom
        send.dateto = self.dateto
      axios
        .post("/api/status_provider", send)
        .then((res) => {
          console.log(res.data.length)
          if(res.data){
          self.statuses = res.data.allstatuses;
          self.hm = res.data.all[0].n;

          }
        })
        .catch((error) => console.log(error));
    },
  },

  components: {
    downloadCsv: JsonCSV,
  },
};
</script>
