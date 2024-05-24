<template>
  <div>
    <v-container fluid>
      <v-row>
        <v-col cols="9">
          <!-- <v-card class="mx-auto"> -->
          <!-- max-width="900" -->
          <v-data-table
            v-model="selected"
            :headers="headers"
            :items="filteredUser"
            sort-by="role_id"
            show-select
            class="border"
            :single-select="true"
          >
            <template v-slot:top>
              <v-toolbar flat>
                <v-toolbar-title>Пользователи</v-toolbar-title>
                <v-divider class="mx-4" inset vertical></v-divider>
                <v-switch
                  hide-details
                  v-model="showActive"
                  :label="`Показывать : ${
                    showActive ? 'Активных' : 'Не активных'
                  }`"
                ></v-switch>
                <v-spacer></v-spacer>
                <v-dialog
                  v-model="dialog"
                  max-width="600px"
                  content-class="dialogtop"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <statusUsers :o_users="selected" />
                    <v-btn
                      color="primary"
                      dark
                      class="mb-2"
                      v-bind="attrs"
                      v-on="on"
                    >
                      Добавить пользователя
                    </v-btn>
                  </template>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitle }}</span>
                    </v-card-title>

                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col cols="6">
                            <v-text-field
                              v-model="editedItem.fio"
                              label="ФИО"
                            ></v-text-field>
                          </v-col>
                          <!-- <v-col cols="6">
                        <img
                          :src="'/storage/' + editedItem.pic"
                          height="50"
                          v-if="
                            (typeof editedItem.pic === 'string' ||
                              editedItem.pic instanceof String) &&
                            editedItem.pic != ''
                          "
                        />
                        <v-file-input
                          label="Картинка"
                          v-model="editedItem.pic"
                        ></v-file-input>
                      </v-col> -->
                          <v-col cols="6">
                            <v-text-field
                              v-model="editedItem.name"
                              label="Логин"
                            ></v-text-field>
                          </v-col>
                          <v-col cols="6">
                            <v-text-field
                              v-model="editedItem.password"
                              label="Пароль"
                            ></v-text-field>
                          </v-col>
                          <v-col cols="6">
                            <v-select
                              :items="roles"
                              v-model="editedItem.role_id"
                              item-text="name"
                              item-value="id"
                              label="Роль"
                            ></v-select>
                          </v-col>
                          <v-col cols="6">
                            <v-select
                              :items="offices"
                              v-model="editedItem.office_id"
                              item-text="name"
                              item-value="id"
                              label="Office"
                            ></v-select>
                          </v-col>
                          <v-col cols="6">
                            <v-autocomplete
                              :items="
                                group.filter((g) => {
                                  return g.office_id == editedItem.office_id;
                                })
                              "
                              v-model="editedItem.group_id"
                              item-text="fio"
                              item-value="id"
                              label="Группа"
                              :menu-props="{ maxHeight: '60vh' }"
                            ></v-autocomplete>
                          </v-col>

                          <v-col cols="12">
                            <v-textarea
                              outlined
                              label="Name;Server;Login;Password;Prefix"
                              v-model="editedItem.servers"
                              value="editedItem.servers"
                            ></v-textarea>
                          </v-col>
                          <v-col cols="4">
                            <v-switch
                              v-model="editedItem.sip"
                              label="sip"
                              color="deep-purple accent-3"
                              hide-details
                            ></v-switch>
                          </v-col>

                          <v-col cols="4">
                            <v-switch
                              hide-details
                              v-model="editedItem.active"
                              :label="`${
                                editedItem.active ? 'Активный' : 'Спрятан'
                              }`"
                            ></v-switch>
                          </v-col>
                          <v-col cols="4">
                            <v-text-field
                              v-model="editedItem.order"
                              label="Номер сортировки"
                            ></v-text-field>
                          </v-col>
                          <v-col cols="4">
                            <v-text-field
                              v-model="editedItem.serv"
                              label="Имя сервера"
                            ></v-text-field>
                          </v-col>
                          <v-col cols="4">
                            <v-text-field
                              v-model="editedItem.user_serv"
                              label="Имя менеджера на сервере (User*)"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" text @click="close">
                        Отмена
                      </v-btn>
                      <v-btn color="blue darken-1" text @click="save">
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px">
                  <v-card>
                    <v-card-title class="headline"
                      >Пользователь станет не активным?</v-card-title
                    >
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" text @click="closeDelete"
                        >Нет</v-btn
                      >
                      <v-btn
                        color="blue darken-1"
                        text
                        @click="deleteItemConfirm"
                        >Да</v-btn
                      >
                      <v-spacer></v-spacer>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <v-col
                  v-if="$attrs.user.role_id == 1 && $attrs.user.office_id == 0"
                >
                  <p>Фильтр office</p>
                  <v-select
                    v-model="filterOffices"
                    :items="offices"
                    item-text="name"
                    item-value="id"
                    outlined
                    rounded
                  >
                  </v-select>
                </v-col>
              </v-toolbar>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon small class="mr-2" @click="editItem(item)">
                mdi-pencil
              </v-icon>
              <v-icon
                v-if="$attrs.user.office_id == 0"
                small
                @click="deleteItem(item)"
              >
                mdi-delete
              </v-icon>
            </template>
            <template v-slot:no-data>
              <v-btn color="primary" @click="getUsers"> Reset </v-btn>
            </template>
          </v-data-table>
          <!-- </v-card> -->
        </v-col>
        <v-col cols="3">
          <v-data-table
            :headers="headers_office"
            :items="offices"
            class="border"
            :single-select="true"
          >
            <template v-slot:top>
              <v-toolbar flat>
                <v-toolbar-title>Offices</v-toolbar-title>
                <v-divider class="mx-4" inset vertical></v-divider>
                <v-spacer></v-spacer>
                <v-dialog v-model="dialogOffice" max-width="600px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn
                      color="primary"
                      dark
                      class="mb-2"
                      v-bind="attrs"
                      v-on="on"
                    >
                      Добавить Office
                    </v-btn>
                  </template>
                  <v-card>
                    <v-card-title>
                      <span class="headline">{{ formTitleOffice }}</span>
                    </v-card-title>

                    <v-card-text>
                      <v-container>
                        <v-row>
                          <v-col cols="12">
                            <v-text-field
                              v-model="editedItemOffice.name"
                              label="Имя"
                            ></v-text-field>
                          </v-col>
                        </v-row>
                      </v-container>
                    </v-card-text>

                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" text @click="closeOffice">
                        Отмена
                      </v-btn>
                      <v-btn color="blue darken-1" text @click="saveOfficeBtn">
                        Сохранить
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-toolbar>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-icon small class="mr-2" @click="editItemOffice(item)">
                mdi-pencil
              </v-icon>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import statusUsers from "./statusUsers";
import axios from "axios";
export default {
  data: () => ({
    pic: null,
    imageUrl: "",
    selected: [],
    dialog: false,
    dialogOffice: false,
    dialogDelete: false,
    users: [],
    group: [{ fio: "Без группы", id: 0 }],
    roles: [
      { id: 1, name: "Administrator" },
      { id: 2, name: "CRM Manager" },
      { id: 3, name: "Manager" },
    ],
    headers: [
      { text: "ID", value: "id" },
      { text: "Логин", value: "name" },
      { text: "ФИО", value: "fio" },
      { text: "Роль", value: "role" },
      { text: "Группа", value: "group" },
      { text: "Office", value: "office" },
      // { text: "Показывать", value: "active" },
      { text: "Действия", value: "actions", sortable: false },
    ],
    headers_office: [
      { text: "Имя", value: "name", sortable: false },
      { text: "", value: "actions", sortable: false },
    ],
    offices: [],
    filterOffices: "",
    editedIndex: -1,
    editedIndexOffice: -1,
    editedItem: {
      id: 0,
      name: "",
      fio: "",
      pic: "",
      role_id: 0,
      password: "",
      group_id: "",
      // sip_server: "",
      // sip_login: "",
      // sip_password: "",
      // sip_prefix: "",
      sip: false,
      servers: "",
      order: 99,
      active: 1,
      user_serv: "",
      serv: "",
    },
    editedItemOffice: {
      id: 0,
      name: "",
    },
    defaultItem: {
      name: "",
      fio: "",
      pic: "",
      role_id: 0,
      password: "",
      group_id: "",
      // sip_server: "",
      // sip_login: "",
      // sip_password: "",
      // sip_prefix: "",
      sip: false,
      servers: "",
      active: 1,
      order: 99,
      user_serv: "",
      serv: "",
    },
    defaultItemOffice: {
      name: "",
    },
    sip: false,
    showActive: true,
  }),

  computed: {
    filteredUser() {
      return this.users.filter((i) => {
        return this.filterOffices == i.office_id && i.active == this.showActive;
      });
    },
    formTitle() {
      return this.editedIndex === -1
        ? "Новый пользователь"
        : "Редактировать пользователя";
    },
    formTitleOffice() {
      return this.editedIndexOffice === -1
        ? "Новый office"
        : "Редактировать office";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogOffice(val) {
      val || this.closeOffice();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
  },

  created() {
    this.getOffices();
  },

  methods: {
    rolename(user) {
      user.role = this.roles.find((r) => r.id == user.role_id).name;
    },
    fio(user) {
      try {
        user.group = this.group.find((el) => {
          return el.id == user.group_id;
        }).fio;
      } catch (error) {
        user.group = "";
      }
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data;
        })
        .then(() => {
          self.users.map(function (u) {
            // u.role = self.roles.find((r) => r.id == u.role_id).name;
            self.rolename(u);
            if (u.role_id == 2)
              self.group.push({ fio: u.fio, id: u.id, office_id: u.office_id });
            u.group = "";
            if (u.office_id != null) {
              u.office =
                self.offices.find((o) => {
                  return o.id == u.office_id;
                }).name || "";
            }
          });
          self.group.sort((a, b) => {
            const fioA = a.fio.toUpperCase(); // ignore upper and lowercase
            const fioB = b.fio.toUpperCase(); // ignore upper and lowercase
            if (fioA < fioB) {
              return -1;
            }
            if (fioA > fioB) {
              return 1;
            }

            // names must be equal
            return 0;
          });
          self.users.map((u) => {
            if (u.group_id > 0) {
              self.fio(u);
            }
          });
        })
        .catch((error) => console.log(error));
    },
    getOffices() {
      let self = this;
      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
          self.filterOffices = self.offices[0].id;
          if (
            self.$attrs.user.role_id == 1 &&
            self.$attrs.user.office_id == 0
          ) {
            self.offices.unshift({ name: "SuperOffice", id: 0 });
          }
          this.getUsers();
        })
        .catch((error) => console.log(error));
    },
    saveUsers(u) {
      let self = this;
      var form_data = new FormData();

      for (var key in u) {
        form_data.append(key, u[key]);
      }
      axios
        .post("/api/user/update", form_data)
        .then((res) => {})
        .catch((error) => console.log(error));
    },
    saveOffice(u) {
      let self = this;
      var form_data = new FormData();

      for (var key in u) {
        form_data.append(key, u[key]);
      }
      axios
        .post("/api/office/update", form_data)
        .then((res) => {})
        .catch((error) => console.log(error));
    },

    editItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    editItemOffice(item) {
      this.editedIndexOffice = this.offices.indexOf(item);
      this.editedItemOffice = Object.assign({}, item);
      this.dialogOffice = true;
    },

    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      axios
        .delete("/api/user/" + this.editedItem.id)
        .then((res) => {})
        .catch((error) => console.log(error));
      this.users.splice(this.editedIndex, 1);
      this.closeDelete();
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeOffice() {
      this.dialogOffice = false;
      this.$nextTick(() => {
        this.editedItemOffice = Object.assign({}, this.defaultItemOffice);
        this.editedIndexOffice = -1;
      });
    },

    closeDelete() {
      this.dialogDelete = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      if (this.editedIndex > -1) {
        delete this.editedItem.role;
        this.saveUsers(this.editedItem);
        this.rolename(this.editedItem);
        Object.assign(this.users[this.editedIndex], this.editedItem);
      } else {
        delete this.editedItem.role;
        this.saveUsers(this.editedItem);
        this.rolename(this.editedItem);
        this.users.push(this.editedItem);
      }
      this.close();
    },
    saveOfficeBtn() {
      console.log(this.editedItemOffice.name, this.offices);
      if (
        this.editedItemOffice.name == null ||
        this.editedItemOffice.name == "" ||
        this.offices.find((el) => el.name == this.editedItemOffice.name)
      )
        return;
      if (this.editedIndexOffice > -1) {
        this.saveOffice(this.editedItemOffice);
        Object.assign(
          this.offices[this.editedIndexOffice],
          this.editedItemOffice
        );
      } else {
        this.saveOffice(this.editedItemOffice);
        this.offices.push(this.editedItemOffice);
      }
      this.closeOffice();
    },
  },
  components: {
    statusUsers,
  },
};
</script>

<style scoped>
>>> .dialogtop {
  align-self: flex-start;
}
</style>
