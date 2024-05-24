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
      <!-- <div v-for="office in offices" :key="office.id"> -->
      <!-- <p class="title" v-if="office.id > 0">{{ office.name }}</p> -->

      <div>
        <v-expansion-panels multiple v-model="panel">
          <v-expansion-panel
            v-for="group in groups"
            :key="group.id"
            :expand="true"
          >
            <v-expansion-panel-header>
              <div class="d-flex align-start">
                <input
                  type="checkbox"
                  class="mr-1"
                  :id="group.id"
                  :value="group.id"
                  @change.stop.prevent="setGroup(group.id, 0, $event)"
                />
                <label :for="group.id"
                  ><b>{{ group.fio }}</b></label
                >
              </div>
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <!-- v-model="akkvalue['H' + group.id]" -->
              <v-expansion-panel :expand="true">
                <v-expansion-panel-header>
                  <div class="d-flex align-start">
                    <input
                      type="checkbox"
                      class="mr-1"
                      :id="'H' + group.id"
                      :value="'H' + group.id"
                      @change.stop.prevent="setGroup(group.id, 3, $event)"
                    />
                    <label :for="'H' + group.id">Hight </label>
                  </div>
                </v-expansion-panel-header>
                <v-expansion-panel-content>
                  <div
                    v-for="huser in users.filter((u) => {
                      return u.group_id == group.id && u.level == 3;
                    })"
                    :key="huser.id"
                  >
                    <input
                      type="checkbox"
                      :id="huser.id"
                      :value="huser.id"
                      v-model="userids"
                    />
                    <label :for="huser.id">{{ huser.fio }}</label>
                  </div>
                </v-expansion-panel-content>
              </v-expansion-panel>
              <!-- v-model="akkvalue['M' + group.id]" -->
              <v-expansion-panel :expand="true">
                <v-expansion-panel-header>
                  <div class="d-flex align-start">
                    <input
                      type="checkbox"
                      class="mr-1"
                      :id="'M' + group.id"
                      :value="'M' + group.id"
                      @change.stop.prevent="setGroup(group.id, 2, $event)"
                    />
                    <label :for="'M' + group.id">Middle</label>
                  </div>
                </v-expansion-panel-header>
                <v-expansion-panel-content>
                  <div
                    v-for="muser in users.filter((u) => {
                      return u.group_id == group.id && u.level == 2;
                    })"
                    :key="muser.id"
                  >
                    <input
                      type="checkbox"
                      :id="muser.id"
                      :value="muser.id"
                      v-model="userids"
                    />
                    <label :for="muser.id">{{ muser.fio }}</label>
                  </div>
                </v-expansion-panel-content>
              </v-expansion-panel>
              <!-- v-model="akkvalue['L' + group.id]" -->
              <v-expansion-panel :expand="true">
                <v-expansion-panel-header>
                  <div class="d-flex align-start">
                    <input
                      type="checkbox"
                      class="mr-1"
                      :id="'L' + group.id"
                      :value="'L' + group.id"
                      @change.stop.prevent="setGroup(group.id, 1, $event)"
                    />
                    <label :for="'L' + group.id">Low</label>
                  </div>
                </v-expansion-panel-header>
                <v-expansion-panel-content>
                  <div
                    v-for="huser in users.filter((u) => {
                      return u.group_id == group.id && u.level == 1;
                    })"
                    :key="huser.id"
                  >
                    <input
                      type="checkbox"
                      :id="huser.id"
                      :value="huser.id"
                      v-model="userids"
                    />
                    <label :for="huser.id">{{ huser.fio }}</label>
                  </div>
                </v-expansion-panel-content>
              </v-expansion-panel>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </div>
      <!-- <v-expansion-panels v-model="akkvalue[office.id]">
          <v-expansion-panel
            v-for="item in group.filter((g) => g.office_id == office.id)"
            :key="item.group_id"
          >
            <v-expansion-panel-header>
              {{ item.fio }}

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
                  </v-badge >
              </label>
              </v-col>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels> -->

      <v-btn class="btn ma-3" @click="setUserIds">Назначить</v-btn>
    </v-list>
  </v-card>
</template>

<script>
import axios from "axios";
export default {
  props: {
    user: {},
  },
  data: () => ({
    users: [],
    offices: [],
    groups: [],
    loading: false,
    // akkvalue: [],
    userids: [],
    panel: [],
  }),
  mounted() {
    this.getUsers();
    // this.getOffices();
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
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
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
          self.users = self.users.filter((u) => {
            return ![1144, 1036].includes(u.group_id);
          });
          self.users = self.users.filter((u) => {
            return u.fio.indexOf("_") == -1;
          });
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
