<template>
  <v-card class="mx-auto" max-width="600">
    <v-data-table
      :headers="headers"
      :items="statuses"
      sort-by="role_id"
      class="elevation-1"
      cols="6"
    >
      <template v-slot:item.name="{ item }">
        <v-chip :color="item.color" dark>
          {{ item.name }}
        </v-chip>
      </template>
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Статусы</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Добавить статус
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.name"
                        label="Наименование"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="4">
                      <v-text-field
                        v-model="editedItem.order"
                        label="Позиция"
                      ></v-text-field>
                      <v-switch
                        v-model="editedItem.active"
                        label="Показывать:"
                      ></v-switch>
                    </v-col>
                    <v-col cols="8">
                      <v-color-picker
                        class="ma-2"
                        canvas-height="100"
                        mode="hexa"
                        value="hexa"
                        v-model="editedItem.color"
                      ></v-color-picker>
                    </v-col>
                    <v-col cols="4"> </v-col>
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
                >Are you sure you want to delete this item?</v-card-title
              >
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeDelete"
                  >Cancel</v-btn
                >
                <v-btn color="blue darken-1" text @click="deleteItemConfirm"
                  >OK</v-btn
                >
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
        <!-- <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon> -->
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="getStatus"> Reset </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import axios from "axios";
export default {
  data: () => ({
    dialog: false,
    dialogDelete: false,
    statuses: [],
    headers: [
      { text: "Наименование", value: "name" },
      { text: "Позиция", value: "order" },
      { text: "Показывать", value: "active" },
      { text: "Действия", value: "actions", sortable: false },
    ],

    editedIndex: -1,
    editedItem: {
      name: "",
      active: 0,
      color:'#fff',
      order:0
    },
    defaultItem: {
      name: "",
      active: 0,
      color:'#fff',
      order:0
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Новый статус" : "Редактировать статус";
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

  created() {
    // this.initialize(),
    this.getStatus();
  },

  methods: {
    getStatus() {
      let self = this;
      axios
        .get("/api/statusall")
        .then((res) => {
          self.statuses = res.data;
        })
        .catch((error) => console.log(error));
    },
    saveStatus(status) {
      let self = this;
      axios
        .post("/api/statuses", status)
        .then((res) => {
          // console.log(res);
        })
        .catch((error) => console.log(error));
    },
    // initialize() {},
    editItem(item) {
      this.editedIndex = this.statuses.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.statuses.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      this.statuses.splice(this.editedIndex, 1);
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
        this.saveStatus(this.editedItem);
        Object.assign(this.statuses[this.editedIndex], this.editedItem);
      } else {
        this.saveStatus(this.editedItem);
        this.statuses.push(this.editedItem);
      }
      this.close();
    },
  },
};
</script>
