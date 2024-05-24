<template>
  <div id="mlids">
    <v-row>
      <v-col cols="12">
        <v-row>
          <v-col cols="2">
            <v-card-title>
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Поиск"
                outlined
                rounded
                @click:append="
                  page = 0;
                  getLidsPost();
                "
              ></v-text-field>
            </v-card-title>
          </v-col>
          <v-col cols="2">
            Запрос по поставщикам
            <v-autocomplete
              v-model="filterProviders"
              :items="providers"
              item-text="name"
              item-value="id"
              outlined
              rounded
              multiple
              @change="
                page = 0;
                getLidsPost();
              "
              :menu-props="{ maxHeight: '80vh' }"
            >
              <template v-slot:selection="{ item, index }">
                <span v-if="index <= 2">{{ item.name }} </span>
                <span v-if="index > 2" class="grey--text text-caption">
                  (+{{ filterProviders.length - 1 }} )
                </span>
              </template>
            </v-autocomplete>
          </v-col>
          <v-col cols="2">
            Запрос по номеру

            <v-text-field
              v-model.lazy.trim="filtertel"
              append-icon="mdi-phone"
              outlined
              rounded
              @click:append="
                page = 0;
                getLidsPost();
              "
            ></v-text-field>
          </v-col>
          <v-col cols="2">
            Запрос по статусу
            <v-select
              v-model="filterStatus"
              :items="filterstatuses"
              item-text="name"
              item-value="id"
              outlined
              rounded
              multiple
              @change="
                page = 0;
                getLidsPost();
              "
              :menu-props="{ maxHeight: '80vh' }"
            >
              <template v-slot:selection="{ item, index }">
                <span v-if="index === 0">{{ item.name }} </span>
                <span v-if="index === 1" class="grey--text text-caption">
                  (+{{ filterStatus.length - 1 }} )
                </span>
              </template>
              <template v-slot:item="{ item, attrs }">
                <v-badge
                  :value="attrs['aria-selected'] == 'true'"
                  color="#7620df"
                  dot
                  left
                >
                  <i
                    :style="{
                      background: item.color,
                      outline: '1px solid grey',
                    }"
                    class="sel_stat mr-4"
                  ></i>
                </v-badge>
                {{ item.name }}
              </template>
            </v-select>
          </v-col>
          <v-col cols="2">
            Сервер
            <v-select
              v-model="selectedServer"
              :items="servers"
              item-text="name"
              item-value="name"
              outlined
              rounded
              return-object
              :change="wp_close"
            >
              <template v-slot:selection="{ item, index }">
                <span v-if="index <= 2">{{ item.name }} </span>
                <span v-if="index > 2" class="grey--text text-caption">
                  (+{{ filterProviders.length - 1 }} )
                </span>
              </template>
            </v-select>
          </v-col>
        </v-row>
        <v-progress-linear
          :active="loading"
          indeterminate
          color="purple"
        ></v-progress-linear>
        <v-row>
          <v-col>
            <v-card :class="{ 'd-none': todayItems.length == 0 }">
              <!-- @click:row="clickrow"
              :expanded="expanded" show-expand  hide-default-header   show-select -->
              <v-data-table
                id="ontime"
                v-model.lazy.trim="selected"
                :headers="headerstime"
                item-key="id"
                :single-select="true"
                :single-expand="true"
                :items="todayItems"
                ref="todaytable"
                @click:row="clickrow"
                :items-per-page="100"
                hide-default-footer
              >
                <template
                  v-slot:item.tel="{ item }"
                  v-if="$props.user.sip == 0"
                >
                  <a
                    class="tel"
                    :href="'sip:' + item.tel"
                    @click.stop="
                      qtytel(item.id);
                      lid_id = item.id;
                    "
                  >
                    {{ item.tel }}
                  </a>
                  <span
                    @click.prevent.stop="
                      qtytel(item.id);
                      wp_call(item);
                    "
                  >
                    <!-- :class="{ active: active_el == item.id }" -->
                    <v-icon small> mdi-headset </v-icon>
                  </span>
                </template>
                <template v-slot:item.tel="{ item }" v-else>
                  <span
                    class="tel"
                    @click.prevent.stop="
                      qtytel(item.id);
                      wp_call(item);
                    "
                  >
                    {{ item.tel }}
                  </span>
                  <span>
                    <a
                      :href="'sip:' + item.tel"
                      @click.stop="
                        qtytel(item.id);
                        lid_id = item.id;
                      "
                    >
                      <v-icon small> mdi-headset </v-icon>
                    </a>
                  </span>
                </template>
                <template v-slot:item.status="{ item }">
                  <div class="status_wrp" @click.stop="openDialog(item)">
                    <b
                      :style="{
                        background: stylecolor(item.status_id),
                        outline: '#999 solid 1px',
                      }"
                    ></b>
                    <span>{{ item.status }}</span>
                  </div>
                </template>
                <template v-slot:item.text="{ item }">
                  <span class="fixwidth" :title="item.text">{{
                    item.text
                  }}</span>
                </template>
                <template v-slot:item.address="{ item }">
                  <v-btn
                    small
                    class="teal lighten-4"
                    @click.stop="copyTo(item.address)"
                    v-if="item.address"
                    >{{ item.address }}</v-btn
                  >
                </template>
                <template v-slot:item.actions="{ item }">
                  <v-icon small @click.stop="deleteTime(item)">
                    mdi-delete
                  </v-icon>
                </template>
                <template v-slot:expanded-item="{ headers, item }">
                  <td :colspan="headers.length" class="blackborder">
                    <v-row>
                      <v-col cols="12">
                        <logtel :lid_id="lid_id" :key="lid_id" />
                      </v-col>
                    </v-row>
                  </td>
                </template>
              </v-data-table>
            </v-card>
            <v-card>
              <!-- show-expand show-select  :expanded="expanded" :search="search"-->
              <v-data-table
                id="maintable"
                v-model.lazy.trim="selected"
                :headers="headers"
                item-key="id"
                :single-select="true"
                :single-expand="true"
                :items="lids"
                :items-per-page="100"
                :hide-default-footer="true"
                ref="datatable"
                @click:row="clickrow"
              >
                <template v-slot:top="{}" v-if="hm > 100">
                  <v-row class="align-center">
                    <v-spacer></v-spacer>

                    <h5 class="mb-0">Всего:{{ hm }}</h5>
                    <v-pagination
                      v-model="page"
                      class="my-4"
                      :length="parseInt(hm / limit) + 1"
                      @input="getLidsPost()"
                      total-visible="10"
                    ></v-pagination>
                    <v-col cols="1">
                      <v-select
                        v-model="limit"
                        rounded
                        class="mt-2 border"
                        :items="[10, 50, 100, 250, 500, 'all']"
                        @change="getLidsPost(1)"
                      ></v-select
                    ></v-col>
                  </v-row>
                </template>

                <template v-slot:item.tel="{ item }">
                  <div class="d-flex justify-space-between">
                    <template v-if="$props.user.sip == 0">
                      <a
                        class="tel"
                        :href="'sip:' + item.tel"
                        @click.stop="
                          qtytel(item.id);
                          lid_id = item.id;
                        "
                      >
                        {{ item.tel }}
                      </a>
                      <span
                        @click.prevent.stop="
                          qtytel(item.id);
                          wp_call(item);
                        "
                      >
                        <v-icon small> mdi-headset </v-icon>
                      </span>
                    </template>
                    <template v-else>
                      <span
                        class="tel"
                        @click.prevent.stop="
                          qtytel(item.id);
                          wp_call(item);
                        "
                      >
                        {{ item.tel }}
                      </span>
                      <span>
                        <a
                          :href="'sip:' + item.tel"
                          @click.stop="
                            qtytel(item.id);
                            lid_id = item.id;
                          "
                        >
                          <v-icon small> mdi-headset </v-icon>
                        </a>
                      </span>
                    </template>
                    <span @click.prevent.stop="openDialogBTC(item)">
                      <v-icon class="bitcoin"> mdi-bitcoin </v-icon>
                    </span>
                  </div>
                </template>

                <template v-slot:item.status="{ item }">
                  <div class="status_wrp mx-1" @click.stop="openDialog(item)">
                    <b
                      :style="{
                        background: stylecolor(item.status_id),
                        outline: '#999 solid 1px',
                      }"
                    ></b>
                    <span>{{ item.status }}</span>
                  </div>
                </template>
                <template v-slot:item.text="{ item }">
                  <span class="fixwidth" :title="item.text">{{
                    item.text
                  }}</span>
                </template>
                <template v-slot:item.address="{ item }">
                  <v-btn
                    small
                    class="teal lighten-4"
                    @click.stop="copyTo(item.address)"
                    v-if="item.address"
                    >{{ item.address }}</v-btn
                  >
                </template>
                <template v-slot:expanded-item="{ headers, item }">
                  <td :colspan="headers.length" class="blackborder">
                    <v-row>
                      <v-col cols="12">
                        <logtel :lid_id="lid_id" :key="componentKey" />
                      </v-col>
                    </v-row>
                  </td>
                </template>
              </v-data-table>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <v-snackbar
      v-model="snackbar"
      top
      rigth
      timeout="6000"
      color="success"
      dark
    >
      {{ message }}
      <template v-slot:action="{ attrs }">
        <v-btn color="white" text v-bind="attrs" @click="snackbar = false">
          Х
        </v-btn>
      </template>
    </v-snackbar>
    <v-dialog v-model="dial" persistent max-width="600px">
      <v-card rounded class="rounded-xl pa-4">
        <v-card-title class="text-h5">
          <!-- @change="putSelectedLidsDB" -->
          <div class="wrp__statuses">
            <template v-for="(status, ikey) in statusesnonew">
              <input
                type="radio"
                :value="status.id"
                :id="'st' + status.id"
                v-model="selectedStatus"
                style="display: none"
                :key="'i' + ikey"
              />
              <label
                :for="'st' + status.id"
                class="status_wrp v-label"
                :key="'l' + ikey"
                :class="{ hideStatus: hideStatus(status.id) }"
              >
                <b
                  :style="{
                    background: status.color,
                    outline: '1px solid grey',
                  }"
                ></b>
                <span>{{ status.name }}</span>
              </label>
            </template>
          </div>
        </v-card-title>

        <v-card-text>
          Сообщение
          <v-textarea
            class="px-2 border mb-4"
            rows="1"
            v-model="text_message"
            :value="text_message"
          ></v-textarea>
          <v-row>
            <v-col v-if="selectedStatus == 10">
              Сумма депозита*
              <v-text-field
                required
                v-model="depozit_val"
                class="border px-2 mb-4"
                @keypress="filter()"
                @paste="paste"
                prepend-inner-icon="mdi-currency-usd"
              ></v-text-field
            ></v-col>
            <v-col class="pt-9">
              <v-btn class="border" @click="getBTC">Отримати BTC</v-btn>
            </v-col>
            <v-col>
              Дата/время
              <div class="border px-2">
                <!-- @input="setTime" -->
                <v-datetime-picker
                  ref="datetime"
                  :time-picker-props="timeProps"
                  :datetime="datetime"
                >
                </v-datetime-picker></div
            ></v-col>
          </v-row>
        </v-card-text>
        <v-divider></v-divider>

        <v-card-actions>
          <v-row>
            <v-col>
              <v-btn
                color="darken-1"
                block
                class="border"
                @click="
                  dial = false;
                  selected = [];
                  closeDialog();
                "
              >
                Відмінити
              </v-btn>
            </v-col>

            <v-col>
              <v-btn
                color="dark primary"
                block
                height="100%"
                :disabled="
                  selectedStatus == 10 && depozit_val < 1 && text_message == ''
                "
                @click="
                  writeText();
                  putSelectedLidsDB();
                  dial = false;
                  closeDialog();
                "
              >
                Відправити
              </v-btn>
            </v-col>
          </v-row>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialog" width="600px">
      <v-card class="pa-7">
        <v-card-text>
          <p v-if="selected[0]"><b>Имя: </b>{{ selected[0].name }}</p>
          <p v-if="selected[0]"><b>Телефон: </b>{{ selected[0].tel }}</p>
          <p v-if="selected[0]"><b>Email: </b>{{ selected[0].email }}</p>
          <v-btn class="border" block color="primary" @click="getBTC"
            >Отримати BTC</v-btn
          >
          <v-simple-table>
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">Date & time</th>
                  <th class="text-center">Adresses</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in a_bts" :key="item.id">
                  <td>{{ item.date_time }}</td>
                  <td>
                    <v-btn
                      small
                      class="teal lighten-4"
                      @click="copyTo(item.address)"
                      v-if="item.address"
                      >{{ item.address }}
                      <v-icon> mdi-content-copy </v-icon></v-btn
                    >
                  </td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-card-text>
        <v-card-actions>
          <v-btn block @click="dialog = false">Закрити</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <div id="copy"></div>
  </div>
</template>

<script>
import XLSX from "xlsx";
import axios from "axios";
import logtel from "./logtel";
export default {
  components: {
    logtel,
  },
  props: ["user"],
  data: () => ({
    loading: false,
    webphone: false,
    timeProps: { format: "24hr" },
    dial: false,
    dialog: false,
    depozit: 0,
    depozit_val: "",
    componentKey: 0,
    text_message: "",
    tel: "",
    lid_id: "",
    expanded: [],
    singleExpand: true,
    datetime: "",
    userid: null,
    users: [],
    disableuser: 0,
    statuses: [],
    statusesnonew: [],
    filterstatuses: [],
    selectedStatus: 0,
    filterStatus: [],
    filterProviders: [],
    providers: [],
    selected: [],
    todayItems: [],
    lids: [],
    a_bts: [],
    search: "",
    filtertel: "",
    headers: [
      { text: "Имя", value: "name" },
      { text: "Email", value: "email" },
      { text: "Телефон.", align: "start", value: "tel" },
      { text: "Афилятор", value: "afilyator" },
      { text: "Поставщик", value: "provider" },
      { text: "Создан", value: "date_created" },
      { text: "Статус", value: "status" },
      { text: "Дата", value: "date" },
      { text: "Депозит", value: "deposit" },
      { text: "Перезвон", value: "ontime" },
      { text: "Сообщение", value: "text" },
      { text: "Адрес", value: "address" },
    ],
    headerstime: [
      { text: "Имя", value: "name" },
      { text: "Email", value: "email" },
      { text: "Телефон.", align: "start", value: "tel" },
      { text: "Афилятор", value: "afilyator" },
      { text: "Поставщик", value: "provider" },
      { text: "Создан", value: "date_created" },
      { text: "Статус", value: "status" },
      { text: "Дата", value: "date" },
      { text: "Депозит", value: "deposit" },
      { text: "Сообщение", value: "text" },
      { text: "Адрес", value: "address" },

      { text: "", value: "actions", sortable: false },
    ],
    parse_header: [],
    sortOrders: {},
    sortKey: "tel",
    hm: 0,
    snackbar: false,
    message: "",
    page: 0,
    limit: 100,
    servers: [],
    selectedServer: {},
  }),
  mounted: function () {
    this.getProviders();
    this.getStatuses();
    this.todaylids();
    this.getServers();
  },
  created() {},
  watch: {
    datetime: function (newval, oldval) {
      if ((newval == null || newval != oldval) && this.lid_id != "") {
        this.setTime();
      }
    },
  },
  computed: {},
  methods: {
    getServers() {
      const self = this;
      axios
        .get("/api/getServers/" + this.$props.user.id)
        .then((res) => {
          self.servers = res.data;
          self.selectedServer = self.servers[0];
        })
        .catch((error) => console.log(error));
    },
    openDialogBTC(item) {
      let self = this;
      self.selected[0] = item;
      self.a_bts = [];
      let data = {};
      data.lid_id = item.id;
      axios
        .post("/api/getAssignedBTC", data)
        .then((res) => {
          self.a_bts = res.data;
          this.dialog = true;
        })
        .catch((error) => console.log(error));
    },
    wp_close() {
      if (this.webphone && !this.webphone.closed) {
        this.webphone.close();
      }
      this.webphone = false;
    },
    wp_call(item) {
      this.copyTo(item.tel);
      if (this.webphone && !this.webphone.closed) {
        const tel = this.selectedServer.prefix.toString() + item.tel;
        this.webphone.webphone_api.call(tel);
        this.webphone.focus();
      } else {
        this.webphone = window.open(
          `/webphone/softphone.html?wp_serveraddress=${encodeURIComponent(
            this.selectedServer.server
          )}&wp_username=${encodeURIComponent(
            this.selectedServer.login
          )}&wp_password=${encodeURIComponent(
            this.selectedServer.password
          )}&wp_callto=${this.selectedServer.prefix.toString() + item.tel}`,
          "softphone",
          "width=400,height=540"
        );
      }
    },
    paste(e) {
      if (e.type === "paste") {
        const clip = e.clipboardData.getData("Text");
        setTimeout(function () {
          e.target.value = clip.replace(/[^0-9]/g, "");
        });
      }
    },
    filter: function (evt) {
      evt = evt ? evt : window.event;
      let expect = evt.target.value.toString() + evt.key.toString();

      if (!/^[-+]?[0-9]*\.?[0-9]*$/.test(expect)) {
        evt.preventDefault();
      } else {
        return true;
      }
    },
    qtytel(id) {
      const self = this;
      let data = {};
      data.lid_id = id;
      data.user_id = this.$props.user.id;
      axios
        .post("/api/qtytel", data)
        .then((res) => {
          // self.lids.find((i) => i.id == data.lid_id).qtytel = res.data;
        })
        .catch((error) => console.log(error));
    },
    copyTo(address) {
      this.message = "Copied to clipboard";
      this.snackbar = true;
      if (address == "") {
        this.message = "Нема вільних адресів";
        return;
      }
      this.changeDateBTC(address);
      if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard api method'
        return navigator.clipboard.writeText(address);
      } else {
        // text area method
        let textArea = document.createElement("textarea");
        textArea.value = address;
        // make the textarea out of viewport
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        return new Promise((res, rej) => {
          // here the magic happens
          document.execCommand("copy") ? res() : rej();
          // document.execCommand("copy") ? res() : rej(),window.prompt("Copy to clipboard: Ctrl+C, Enter", address);
          textArea.remove();
        });
      }
    },
    changeDateBTC(address) {
      const self = this;
      let data = {};
      data.address = address;
      axios
        .post("/api/changeDateBTC", data)
        .catch((error) => console.log(error));
    },
    getBTC() {
      const self = this;
      let data = {};
      data.id = this.selected[0].id;
      data.user_id = this.selected[0].user_id;
      data.tel = this.selected[0].tel;
      //get new BTC from table for lead
      axios
        .post("/api/getBTC", data)
        .then((res) => {
          if (res.data.address == undefined) {
            self.copyTo("");
            return;
          }
          self.lids.find((i) => i.id == data.id).address = res.data.address;
          self.a_bts.unshift({ date_time: "", address: res.data.address });
          self.copyTo(res.data.address);
        })
        .catch((error) => console.log(error));
    },
    hideStatus(id) {
      // show only deposit
      if (this.selected.length && this.selected[0].status_id == 10) {
        if (id != 10) {
          return true;
        }
      }
      return false;
    },
    writeText() {
      if (this.text_message.length > 0) {
        (
          this.lids.find((i) => i.id == this.lid_id) ||
          this.todayItems.find((i) => i.id == this.lid_id)
        ).text = this.text_message;
      }
    },
    openDialog(i) {
      let self = this;
      if (self.selected.length > 0 && self.selected[0].id != i.id) {
        this.selected = this.expanded = [];
      }
      this.text_message = "";
      this.lid_id = i.id;
      this.selected = [i];
      this.selectedStatus = i.status_id;
      this.expanded = this.selected;
      self.dial = true;
      setTimeout(() => {
        let self2 = self;
        self2.$refs.datetime.date =
          i.ontime != null ? i.ontime.substring(0, 10) : "";
        self2.$refs.datetime.time =
          i.ontime != null ? i.ontime.substring(11, 16) : "";
      }, 400);
    },
    closeDialog() {
      this.$refs.datetime.date = "";
      this.$refs.datetime.time = "";
      this.selected = [];
      this.selectedStatus = 0;
    },
    getHm() {
      let self = this;
      axios
        .get("/api/getHmLidsUser/" + self.$props.user.id)
        .then((res) => {
          if (res.status == 200) {
            self.hm = res.data;
            if (localStorage.hm) {
              if (localStorage.hm == self.hm) {
                return;
              } else {
                self.message = "У вас изменилось количество лидов!";
                self.snackbar = true;
                localStorage.hm = self.hm;
              }
            }
          }
        })
        .catch((error) => console.log(error));
    },
    nextdep(status_id) {
      if (status_id != 10) return;
      this.depozit = true;
    },
    forceRerender() {
      this.componentKey += 1;
    },
    deleteTime(item) {
      let self = this;
      let send = {};
      send.ontime = null;
      send.id = item.id;
      axios
        .post("api/Lid/ontime", send)
        .then(function (response) {
          item.ontime = null;
          self.todaylids();
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    setTime() {
      const self = this;
      let send = {};
      if (
        this.$refs.datetime == undefined ||
        this.$refs.datetime.formattedDatetime == ""
      )
        return;
      send.ontime = this.$refs.datetime.formattedDatetime;
      send.id = this.lid_id;

      axios
        .post("api/Lid/ontime", send)
        .then(function (response) {
          self.$refs.datetime.clearHandler();
          self.getLidsPost();
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    currentDateTime() {
      const date = new Date();
      // 01, 02, 03, ... 29, 30, 31
      const dd = (date.getDate() < 10 ? "0" : "") + date.getDate();
      // 01, 02, 03, ... 10, 11, 12
      const MM = (date.getMonth() + 1 < 10 ? "0" : "") + (date.getMonth() + 1);
      // 1970, 1971, ... 2015, 2016, ...
      const yyyy = date.getFullYear();
      const time = date.getHours() + ":" + date.getMinutes();
      // create the format you want
      return yyyy + "-" + MM + "-" + dd + " " + time;
    },
    clickrow(i, row) {
      this.$refs.todaytable.expansion = {};
      this.$refs.datatable.expansion = {};

      this.lid_id = i.id;
      row.expand(!row.isExpanded);
    },
    putSelectedLidsDB() {
      const self = this;
      let send = {};
      let send_el = {};
      let eli = {};
      let st = {};
      eli =
        self.lids.find((i) => i.id == self.selected[0].id) ||
        self.todayItems.find((i) => i.id == self.selected[0].id);
      st = self.statuses.find((s) => s.id == self.selectedStatus);
      eli.status = st.name;
      eli.status_id = self.selectedStatus;
      eli.updated_at = self.currentDateTime();
      send.id = eli.id;
      send_el.id = eli.id;
      send_el.tel = eli.tel;
      send_el.text = self.text_message;
      send_el.status_id = self.selectedStatus;
      send_el.user_id = eli.user_id;
      send.data = [];
      send.data.push(send_el);
      axios
        .post("api/Lid/updatelids", send)
        .then(function (response) {
          self.getLidsPost();
          self.forceRerender();
        })
        .catch(function (error) {
          console.log(error);
        });
      this.setTime();
      if (this.depozit_val > 0 && self.selectedStatus == 10) {
        self.setDepozit();
      }
    },

    setDepozit() {
      let self = this;
      let send = {};
      send.lid_id = this.selected[0].id;
      send.user_id = this.selected[0].user_id;
      send.depozit = this.depozit_val;
      axios
        .post("api/setDepozit", send)
        .then(function (response) {
          self.depozit_val = "";
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    usercolor(user) {
      return user.role_id == 2 ? "green" : "blue";
    },

    getStatuses() {
      let self = this;
      axios
        .get("/api/statuses")
        .then((res) => {
          self.statuses = res.data.map(({ name, id, color }) => ({
            name,
            id,
            color,
          }));
          self.statusesnonew = self.statuses.filter((e) => e.id != 8);
          self.filterstatuses = self.statuses.map((e) => e);
          self.getLidsPost();
        })
        .catch((error) => console.log(error));
    },
    getLidsPost(p = this.page) {
      const id = this.$props.user.id;
      let self = this;
      let data = {};

      self.loading = true;
      self.disableuser = id;

      data.id = id;
      data.provider_id = self.filterProviders;
      data.status_id = self.filterStatus;
      data.tel = self.filtertel;
      data.search = self.search;
      data.limit = self.limit;
      data.page = p;

      axios
        .post("/api/getLidsPost", data)
        .then((res) => {
          self.hm = res.data.hm;
          self.lids = Object.entries(res.data.lids).map((e) => e[1]);

          self.lids.map(function (e) {
            if (e.updated_at) {
              e.date = e.updated_at.substring(0, 10);
            }
            e.date_created = e.created_at.substring(0, 10);
            try {
              e.status =
                self.statuses.find((s) => s.id == e.status_id).name || "";
            } catch (error) {
              e.status = "";
            }

            if (self.providers.find((p) => p.id == e.provider_id)) {
              e.provider = self.providers.find(
                (p) => p.id == e.provider_id
              ).name;
            }
          });
          self.todaylids();
          self.loading = false;
        })
        // .then(
        //   () =>
        //     function (e) {
        //       self.lids.map(function (e) {
        //         e.provider = self.providers.find(
        //           (p) => p.id == e.provider_id
        //         ).name;
        //       });
        //     }
        // )
        .catch((error) => console.log(error));
    },
    getLids(id) {
      let self = this;
      let data = {};
      self.search = "";
      self.filtertel = "";
      self.disableuser = id;
      data.id = id;
      data.provider_id = self.filterProviders;
      data.status_id = self.filterStatus;
      data.tel = self.filtertel;
      data.search = self.search;
      axios
        .get("/api/userLids/" + id)
        .then((res) => {
          self.lids = Object.entries(res.data).map((e) => e[1]);
          self.lids.map(function (e) {
            e.date = e.updated_at.substring(0, 10);
            e.date_created = e.created_at.substring(0, 10);

            try {
              e.status =
                self.statuses.find((s) => s.id == e.status_id).name || "";
            } catch (error) {
              e.status = "";
            }

            if (self.providers.find((p) => p.id == e.provider_id)) {
              e.provider = self.providers.find(
                (p) => p.id == e.provider_id
              ).name;
            }
          });

          self.todaylids();
        })
        // .then(
        //   () =>
        //     function (e) {
        //       self.lids.map(function (e) {
        //         e.provider = self.providers.find(
        //           (p) => p.id == e.provider_id
        //         ).name;
        //       });
        //     }
        // )
        .catch((error) => console.log(error));
    },
    todaylids() {
      const self = this;
      const id = self.$props.user.id;
      self.todayItems = [];
      axios
        .get("/api/todaylids/" + id)
        .then((res) => {
          self.todayItems = res.data;
          self.todayItems.map(function (t) {
            if (t.ontime.length > 5) {
              t.date = new Date(t.ontime).toLocaleTimeString().substring(0, 5);
            }
            t.date_created = t.created_at.substring(0, 10);
            if (self.providers.find((p) => p.id == t.provider_id)) {
              t.provider = self.providers.find(
                (p) => p.id == t.provider_id
              ).name;
            }
          });

          self.todayItems.sort(function (a, b) {
            if (a.date > b.date) {
              return 1;
            }
            if (a.date < b.date) {
              return -1;
            }
            // a должно быть равным b
            return 0;
          });
        })
        .catch((error) => console.log(error));
    },
    stylecolor(status_id) {
      if (status_id == null) return;
      let color = "";
      try {
        color = this.statuses.find((e) => e.id == status_id).color;
      } catch (error) {}
      return color;
    },
    getProviders() {
      let self = this;
      axios
        .get("/api/provider")
        .then((res) => {
          self.providers = res.data.map(({ name, id }) => ({ name, id }));
        })
        .catch((error) => console.log(error));
    },
  },
};
</script>

<style scoped>
.tel:hover {
  cursor: url(/img/phone-forward.svg) 10 10, none;
  text-decoration: none;
}
.tel {
  display: inline-block;
  margin-right: 1rem;
  color: #000;
}
.tel.active {
  color: #7620df;
  font-weight: bold;
}
#maintable.v-data-table >>> tr {
  outline: 2px solid transparent;
}

td .status_wrp {
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
  font-size: 14px;
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
.blackborder .row .col {
  margin-top: 1rem;
}
#maintable .v-data-table__wrapper tr td:last-child,
#ontime .v-data-table__wrapper tr td:last-child {
  width: 120px;
}
.fixwidth {
  width: 120px;
  height: 45px;
  overflow: hidden;
  display: block;
}
.hideStatus {
  display: none;
}
.bitcoin {
  color: yellow;
  margin: 0 1rem;
  background: #000;
  cursor: pointer;
}
</style>
