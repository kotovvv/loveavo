<template>
  <v-app id="inspire">
    <v-navigation-drawer
      v-model="drawer"
      fixed
      parmament
      dark
      :mini-variant="true"
      width="64px"
      :app="true"
    >
      <!-- menu -->
      <v-list>
        <v-subheader></v-subheader>
        <v-list-item-group v-model="selectedItem" color="primary">
          <v-list-item
            v-for="(item, i) in items"
            :key="i"
            @click="managerMenu = item.name"
          >
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title></v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>

        <v-list-item-group class="mt-10">
          <v-list-item @click="$emit('login', {})">
            <v-list-item-icon>
              <v-icon>mdi-logout</v-icon>
            </v-list-item-icon>
          </v-list-item>
          <v-list-item>
            <v-list-item-content>
              <v-list-item-title>{{ user.fio }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </v-list>
    </v-navigation-drawer>

    <v-main class="lighten-2">
      <v-container fluid>
        <!-- <v-row> -->
        <!-- table -->
        <component :user="$props.user" :is="managerComponent" />
        <!-- <tablenewlid></tablenewlid> -->
        <!-- </v-row> -->
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
// const lids = () => import("../crmanager/lids.vue");
const lids3 = () => import("./lids3.vue");
const mlids = () => import("../manager/mlids.vue");
const report = () => import("../manager/report.vue");
const calls = () => import("./calls");

export default {
  props: ["user"],
  data: () => ({
    drawer: null,
    selectedItem: 0,
    managerMenu: "lids",
    items: [
      // { text: "Распределение", name: "lids", icon: "mdi-account-arrow-left" },
      { text: "Звонки", name: "calls", icon: "mdi-headset-dock" },
      { text: "Распределение3", name: "lids3", icon: "mdi-sitemap" },
      { text: "Управление", name: "mlids", icon: "mdi-phone-log-outline" },
      { text: "Отчёты", name: "report", icon: "mdi-timetable" },
    ],
  }),
  computed: {
    managerComponent() {
      // if (this.managerMenu == "lids") return lids;
      if (this.managerMenu == "calls") return calls;
      if (this.managerMenu == "lids3") return lids3;
      if (this.managerMenu == "mlids") return mlids;
      if (this.managerMenu == "report") return report;
    },
  },
  methods: {},
};
</script>
