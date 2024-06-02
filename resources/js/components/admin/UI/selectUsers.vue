<template>
  <v-card height="100%" class="pa-5">
    Укажите пользователя для лидов
    <p v-if="userids.length">Выбрано: {{ userids.length }}</p>
    <v-progress-linear
      :active="loading"
      indeterminate
      color="purple"
    ></v-progress-linear>
    <v-list>
      <div v-for="office in offices" :key="office.id">
        <p class="title" v-if="office.id > 0">{{ office.name }}</p>
        <v-expansion-panels v-model="akkvalue[office.id]">
          <v-expansion-panel
            v-for="item in groups.filter((g) => g.office_id == office.id)"
            :key="item.group_id"
          >
            <v-expansion-panel-header>
              {{ item.fio }}
              <div></div>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-col
                v-for="user in users.filter(function (i) {
                  return i.group_id == item.group_id;
                })"
                :key="user.id"
              >
                <input
                  type="checkbox"
                  :id="user.id"
                  :value="user.id"
                  v-model="userids"
                />
                <label :for="user.id"
                  >{{ user.fio }}
                  <v-badge
                    :content="user.hmlids"
                    :value="user.hmlids"
                    :color="usercolor(user)"
                    overlap
                  >
                    <!-- <v-icon large v-if="user.role_id === 2">
                              mdi-account-group-outline
                            </v-icon>
                            <v-icon large v-else> mdi-account-outline </v-icon> -->
                  </v-badge></label
                >
              </v-col>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </div>

      <v-btn class="btn ma-3" @click="setUserIds">Назначить</v-btn>
    </v-list>
  </v-card>
</template>

<script>
import axios from "axios";
import _ from "lodash";
export default {
  props: {
    user: {},
  },
  data: () => ({
    users: [],
    offices: [],
    groups: [],
    loading: false,
    akkvalue: [],
    userids: [],
    panel: [],
    groupByOffice: [],
  }),
  mounted() {
    this.getOffices();
  },
  computed: {},
  methods: {
    setGroup(group_id, level, e) {
      let au = [];
      if (level == 0) {
        au = this.users
          .filter((u) => {
            return u.group_id == group_id;
          })
          .map(({ id }) => id);
      } else {
        au = this.users
          .filter((u) => {
            return u.group_id == group_id && u.level == level;
          })
          .map(({ id }) => id);
      }

      if (e.target.checked) {
        this.userids = this.userids.concat(au);
      } else {
        this.userids = this.userids.filter((u) => {
          return !au.includes(u);
        });
      }
    },
    setUserIds() {
      this.$emit("getUserIds", this.userids);
      this.userids = [];
    },
    getOffices() {
      let self = this;
      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
          // if (self.$props.user.role_id == 1) {
          //   self.offices.unshift({ id: 0, name: "--выбор--" });
          //   self.filterOffices = self.offices[1].id;
          // }
          if (self.$props.user.office_id > 0) {
            self.offices = self.offices.filter(
              (o) => o.id == self.$props.user.office_id
            );
          }
          self.getUsers();
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          let data = res.data;
          self.users = data.map(
            ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
              level,
            }) => ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
              level,
            })
          );
          self.loading = false;
          self.groups = _.filter(self.users, function (o) {
            return o.group_id == o.id;
          });
        })
        .catch((error) => console.log(error));
    },
    getUsers_del() {
      let self = this;
      this.loading = true;
      axios
        .post("/api/getusers", [])
        .then((res) => {
          let data = res.data;
          self.users = data.map(
            ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
              level,
            }) => ({
              name,
              id,
              role_id,
              fio,
              hmlids,
              group_id,
              office_id,
              level,
            })
          );
          self.loading = false;
          // self.users = self.users.filter((u) => {
          //   return ![1144, 1036].includes(u.group_id);
          // });
          // self.users = self.users.filter((u) => {
          //   return u.fio.indexOf("_") == -1;
          // });
          // self.groupByOffice = _.groupBy(self.users, "office_id");
          self.groups = _.filter(self.users, function (o) {
            return o.group_id == o.id;
          });
        })
        .catch((error) => console.log(error));
    },
    deb(u) {
      console.log(u);
    },
    usercolor(user) {
      return user.role_id == 2 ? "green" : "blue";
    },
  },
};
</script>

<style>
.v-expansion-panel-header--active > div > label {
  font-weight: bold;
}
</style>
