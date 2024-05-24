<template>
  <div>
    <v-row>
      <v-col cols="4">
        <h2>Месяц</h2>
        <div>
          <ve-line :data="chartData" :settings="settings"></ve-line>
        </div>
      </v-col>
      <v-col cols="8">
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="text-left">FTD</th>
                <th class="text-left">Сегодня</th>
                <th class="text-left">Количество наборов</th>
                <th class="text-left">Общее время разговоров</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ todayReport.ftd }}</td>
                <td>
                  <span class="h5">{{ todayReport.sum }}</span
                  ><span v-if="todayReport.sum" class="h5">$</span>
                </td>
                <td>{{ todayReport.hmcall }}</td>
                <td>{{ todayReport.alltimecall }}</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="text-left">FTD</th>
                <th class="text-left">Месяц</th>
                <th class="text-left">Количество наборов</th>
                <th class="text-left">Общее время разговоров</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ monthReport.ftd }}</td>
                <td>
                  <span class="h5">{{ monthReport.sum }}</span
                  ><span v-if="monthReport.sum" class="h5">$</span>
                </td>
                <td>{{ monthReport.hmcall }}</td>
                <td>{{ monthReport.alltimecall }}</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
        <div class="mt-5" v-if="StatusesMonth.length">
          <v-card-title primary-title>
            <h3>Статусы</h3>
          </v-card-title>
          <v-card-actions>
            <div class="wrp__statuses">
              <template v-for="i in StatusesMonth">
                <div class="status_wrp">
                  <b
                    :style="{
                      background: i[1][0].color,
                      outline: '#999 solid 1px',
                    }"
                    >{{ i[1].length }}</b
                  >
                  <span>{{ i[0] }}</span>
                </div>
              </template>
            </div>
          </v-card-actions>
        </div>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
import _ from "lodash";
// import VeLine from "v-charts/lib/line.common";
import VeLine from "v-charts/lib/bar.common";
import "v-charts/lib/style.css";
export default {
  components: { VeLine },
  data: () => ({
    settings: {
      xAxisType: "value",
    },
    todayReport: {
      ftd: "",
      sum: "",
      hmcall: "",
      alltimecall: "",
    },
    monthReport: {
      ftd: "",
      sum: "",
      hmcall: "",
      alltimecall: "",
    },
    BalansMonth: {},
    StatusesMonth: {},
    DepozitsMonth: {},

    chartData: {
      columns: ["date", "balans"],

      rows: [],
    },
  }),
  mounted: function () {
    this.getBalansMonth();
    this.getStatusesMonth();
    this.getDepozitsMonth();
    this.getCallsMonth();
  },
  methods: {
    monthStatus() {},
    getBalansMonth() {
      let self = this;
      self.chartData.rows = [];
      axios
        .get("api/getBalansMonth/" + self.$attrs.user.id)
        .then((res) => {
          self.BalansMonth = res.data;
          self.todayReport.sum = _.sumBy(
            _.filter(res.data, { date: new Date().toISOString().slice(0, 10) }),
            "balans"
          );
          self.monthReport.sum = _.sumBy(res.data, "balans");
          self.BalansMonth.map((i, ix) => {
            self.chartData.rows.push({ balans: i.balans, date: i.date });
          });
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getStatusesMonth() {
      let self = this;
      axios
        .get("api/getStatusesMonth/" + self.$attrs.user.id)
        .then((res) => {
          self.StatusesMonth = res.data;
          self.monthReport.ftd = _.filter(self.StatusesMonth, {
            name: "Deposit",
          }).length;
          self.StatusesMonth = Object.entries(
            _.groupBy(self.StatusesMonth, "name")
          );
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getDepozitsMonth() {
      let self = this;
      axios
        .get("api/getDepozitsMonth/" + self.$attrs.user.id)
        .then((res) => {
          self.DepozitsMonth = res.data;
          self.todayReport.ftd = _.filter(self.DepozitsMonth, (d) => {
            return (
              d.created_at.slice(0, 10) == new Date().toISOString().slice(0, 10)
            );
          }).length;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getCallsMonth() {
      let self = this;
      axios
        .get("api/getCallsMonth/" + self.$attrs.user.id)
        .then((res) => {
          //  console.log(res.data.callmonth[0])
          self.monthReport.hmcall = res.data.callmonth[0].count;
          let t = (res.data.callmonth[0].duration / 60 / 60)
            .toFixed(2)
            .toString()
            .split(".");
          self.monthReport.alltimecall = t[0] + " час. " + t[1] + " мин.";
          self.todayReport.hmcall = res.data.callday[0].count;
          t = (res.data.callday[0].duration / 60 / 60)
            .toFixed(2)
            .toString()
            .split(".");
          self.todayReport.alltimecall = t[0] + " час. " + t[1] + " мин.";
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>

<style scoped>
</style>
