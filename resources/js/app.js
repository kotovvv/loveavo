import Vue from "vue";
import vuetify from "./vuetify";
import DatetimePicker from "vuetify-datetime-picker";
import "material-design-icons-iconfont/dist/material-design-icons.css";
import "@mdi/font/css/materialdesignicons.min.css";
// Vue.component('manager-component', require('./components/manager/main.vue').default);
// Vue.component('datetime-picker'), DatetimePicker
Vue.component("main-component", require("./Main").default);

Vue.use(DatetimePicker);

new Vue({
  vuetify,
}).$mount("#app");
