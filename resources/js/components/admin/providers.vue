<template>
  <v-card class="mx-auto" max-width="500">
    <v-data-table
      :headers="headers"
      :items="providers"
      sort-by="role_id"
      class="elevation-1"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Поставщики</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog
            v-model="dialog"
            max-width="500px"
            content-class="dialogtop"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Добавить поставщика
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">
                  Отмена
                </v-btn>
                <v-btn color="blue darken-1" text @click="save">
                  Сохранить
                </v-btn>
              </v-card-actions>
              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="6">
                      <v-text-field
                        v-model="editedItem.name"
                        label="Наименование"
                      ></v-text-field>
                    </v-col>
                    <v-text-field
                      v-model="editedItem.password"
                      label="Пароль"
                    ></v-text-field>
                    <!-- <v-col cols="6">
                    <v-switch
                      v-model="editedItem.active"
                      label="Показывать:"
                    ></v-switch>
                  </v-col> -->
                    <v-col cols="6">
                      <v-select
                        multiple
                        :items="offices"
                        v-model="editedItem.office_id"
                        item-text="name"
                        item-value="id"
                        label="Office"
                      ></v-select>
                    </v-col>
                    <v-col cols="6">
                      <v-autocomplete
                        multiple
                        :items="
                          users.filter((u) => {
                            return editedItem.office_id.includes(u.office_id);
                          })
                        "
                        v-model="editedItem.related_users_id"
                        item-text="name"
                        item-value="id"
                        label="Связанные пользователи"
                      >
                        <template v-slot:selection="{ item, index }">
                          <span v-if="index === 0">{{ item.name }} </span>
                          <span
                            v-if="index === 1"
                            class="grey--text text-caption"
                          >
                            +
                          </span>
                        </template>
                        <template v-slot:item="{ item, attrs }">
                          <v-badge
                            :value="attrs['aria-selected'] == 'true'"
                            color="#7620df"
                            dot
                            left
                          >
                            {{ item.name }}
                          </v-badge>
                        </template>
                      </v-autocomplete>
                    </v-col>
                    <v-col cols="12">
                      <v-select
                        :items="users"
                        v-model="editedItem.user_id"
                        item-text="name"
                        item-value="id"
                        label="Пользователь для импорта"
                      ></v-select>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.tel"
                        label="ApiKey"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="headline">Удалить поставщика?</v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeDelete"
                  >Нет</v-btn
                >
                <v-btn color="blue darken-1" text @click="deleteItemConfirm"
                  >Да</v-btn
                >
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
        <v-icon
          small
          v-if="$attrs.user.role_id == 1 && $attrs.user.office_id == 0"
          @click="deleteItem(item)"
        >
          mdi-delete
        </v-icon>
      </template>
      <template v-slot:item.report="{ item }">
        <statusesProvider :provider="item" />
      </template>
      <!-- <template v-slot:item.report="{ item }">
      <v-icon small class="mr-2" @click="report(item)"> mdi-file-chart-outline </v-icon>
      <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
    </template> -->
      <template v-slot:no-data>
        <v-btn color="primary" @click="getProvider"> Reset </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import statusesProvider from "./statusProvider";
import axios from "axios";
export default {
  data: () => ({
    provider: {},
    dialog: false,
    dialogDelete: false,
    providers: [],
    users: [],
    headers: [
      { text: "ID", value: "id" },
      { text: "Наименование", value: "name" },

      { text: "Редактировать", value: "actions", sortable: false },
      { text: "Отчёт", value: "report", sortable: false },
    ],

    editedIndex: -1,
    editedItem: {
      name: "",
      password: "",
      active: 1,
      related_users_id: [],
      office_id: [],
      user_id: 0,
    },
    defaultItem: {
      name: "",
      password: "",
      active: 1,
      related_users_id: [],
      office_id: [],
      user_id: 0,
    },
    offices: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "Новый поставщик"
        : "Редактировать поставщика";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
  },

  mounted() {
    // this.initialize(),
    this.getOffices();
    this.getUsers();
    this.getProvider();
  },

  methods: {
    getOffices() {
      let self = this;
      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data;
        })
        .catch((error) => console.log(error));
    },
    report(item) {
      if (this.provider == item) {
        this.provider = {};
        return;
      }
      this.provider = item;
    },
    getProvider() {
      let self = this;
      axios
        .get("/api/providerall")
        .then((res) => {
          self.providers = res.data;
          self.providers = self.providers.map(function (p) {
            if (p.related_users_id.length > 0)
              p.related_users_id = JSON.parse(p.related_users_id);
            if (p.office_id.length > 0) p.office_id = JSON.parse(p.office_id);
            return p;
          });
        })
        .catch((error) => console.log(error));
    },
    saveProvider(provider) {
      let self = this;
      axios
        .post("/api/provider", provider)
        .then((res) => {
          if (provider.id == undefined) {
            let idx = self.providers.indexOf(provider);
            Object.assign(self.providers[idx], res.data.provider);
          }
        })
        .catch((error) => console.log(error));
    },
    editItem(item) {
      this.editedIndex = this.providers.indexOf(item);
      this.editedItem = Object.assign({}, item);
      if (!Array.isArray(item.related_users_id))
        this.editedItem.related_users_id = [];
      if (!Array.isArray(item.office_id)) this.editedItem.office_id = [];
      this.dialog = true;
    },
    deleteItem(item) {
      this.editedIndex = this.providers.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      axios
        .delete("/api/provider/" + this.editedItem.id)
        .then((res) => {
          // console.log(res);
        })
        .catch((error) => console.log(error));
      this.providers.splice(this.editedIndex, 1);
      this.closeDelete();
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
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
        this.saveProvider(this.editedItem);
        Object.assign(this.providers[this.editedIndex], this.editedItem);
      } else {
        this.saveProvider(this.editedItem);
        this.providers.push(this.editedItem);
      }
      this.close();
    },
  },
  components: {
    statusesProvider,
  },
};
</script>
<style scoped>
>>> .dialogtop {
  align-self: flex-start;
}
</style>
