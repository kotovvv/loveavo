 <template>
  <div class="csv">
    <v-snackbar v-model="snackbar" top right timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <v-tabs v-model="tab" background-color="primary" dark>
      <v-tab> XLSX </v-tab>
      <v-tab> CSV </v-tab>

      <v-tab v-if="$attrs.user.role_id == 1 && $attrs.user.group_id == 0">
        ВТС
      </v-tab>
      <v-tab>CHECK DUBLIKATE MAIL</v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab">
      <v-tab-item>
        <importxlsx :user="$attrs.user"></importxlsx>
      </v-tab-item>
      <v-tab-item>
        <!--   <v-row>
          <v-col cols="2">
            <v-select
              v-model="selectedProvider"
              :items="providers"
              label="Провайдер"
              item-text="name"
              item-value="id"
            ></v-select>
          </v-col>
          <v-col cols="3" v-if="selectedProvider">
            <v-file-input
              v-model="files"
              ref="fileupload"
              label="загрузить CSV"
              show-size
              truncate-length="24"
              @change="onFileChange"
            ></v-file-input>
          </v-col>
          <v-col cols="3" v-if="parse_csv.length">
            <v-select
              v-model="selectedStatus"
              :items="statuses"
              label="Статус"
              item-text="name"
              item-value="id"
            ></v-select>
          </v-col>
          <v-col cols="4" v-if="parse_csv.length">
            <v-textarea
              v-model="message"
              label="Сообщение"
              rows="1"
            ></v-textarea>
          </v-col>
        </v-row> -->
        <v-progress-linear
          :active="loading"
          indeterminate
          color="purple"
        ></v-progress-linear>
        <v-main v-if="parse_csv.length && files">
          <v-row class="csv">
            <v-col cols="8">
              <v-card>
                <v-container>
                  <v-row>
                    <v-col cols="4">
                      <v-card-title>
                        <v-text-field
                          v-model="search"
                          append-icon="mdi-magnify"
                          label="Поиск"
                          single-line
                          hide-details
                        ></v-text-field>
                      </v-card-title>
                    </v-col>
                    <v-col cols="3">
                      <v-card-title>
                        <v-text-field
                          v-model.lazy.trim="filtertel"
                          append-icon="mdi-phone"
                          label="Начальные цифры"
                          single-line
                          hide-details
                        ></v-text-field>
                      </v-card-title>
                    </v-col>
                  </v-row>
                </v-container>
                <v-data-table
                  v-model.lazy.trim="selected"
                  :headers="headers"
                  :search="search"
                  :single-select="false"
                  item-key="tel+afilyator"
                  show-select
                  :items="filteredItems"
                  ref="datatable"
                  :loading="loading"
                ></v-data-table>
              </v-card>
            </v-col>
            <v-col cols="4">
              <v-card height="100%" class="pa-5">
                Укажите пользователя для лидов
                <v-list>
                  <v-radio-group
                    @change="putSelectedLidsDB"
                    ref="radiogroup"
                    v-model="userid"
                    v-bind="users"
                    id="usersradiogroup"
                  >
                    <v-radio
                      :value="user.id"
                      v-for="user in users"
                      :key="user.id"
                    >
                      <template v-slot:label>
                        {{ user.fio }}
                        <v-badge
                          :content="user.hmlids"
                          :value="user.hmlids"
                          :color="usercolor(user)"
                          overlap
                        >
                          <v-icon large v-if="user.role_id === 2">
                            mdi-account-group-outline
                          </v-icon>
                          <v-icon large v-else> mdi-account-outline </v-icon>
                        </v-badge>
                      </template>
                    </v-radio>
                  </v-radio-group>
                </v-list>
              </v-card>
            </v-col>
          </v-row>
        </v-main>
        <v-row v-else>
          <v-col cols="12">
            <v-row class="align-center pt-10">
              <v-col cols="1">
                <v-menu
                  v-model="dateFrom"
                  :close-on-content-click="false"
                  :nudge-right="40"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="datetimeFrom"
                      label="С даты"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    locale="ru-ru"
                    v-model="datetimeFrom"
                    @input="
                      dateFrom = false;
                      takedates ? getImports() : '';
                    "
                  ></v-date-picker>
                </v-menu>
              </v-col>
              <v-col cols="1">
                <v-menu
                  v-model="dateTo"
                  :close-on-content-click="false"
                  :nudge-right="40"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="datetimeTo"
                      label="По дату"
                      readonly
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    locale="ru-ru"
                    v-model="datetimeTo"
                    @input="
                      dateTo = false;
                      takedates ? getImports() : '';
                    "
                  ></v-date-picker>
                </v-menu>
              </v-col>
              <v-checkbox v-model="takedates" @change="getImports"></v-checkbox>

              <v-col cols="2">
                <v-autocomplete
                  v-model="filter_import_provider"
                  :items="i_providers"
                  label="Фильтр провайдер"
                  item-text="name"
                  item-value="id"
                  outlined
                  rounded
                  multiple
                  clearable="clearable"
                >
                  <template v-slot:selection="{ item, index }">
                    <span v-if="index === 0">{{ item.name }} </span>
                    <span v-if="index === 1" class="grey--text text-caption">
                      (+{{ filterProviders.length - 1 }} )
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
              <v-col cols="2">
                <v-select
                  ref="resetStatus"
                  label="Переназначить статусы"
                  v-model="resetStatus"
                  :items="statuses"
                  item-text="name"
                  item-value="id"
                  outlined
                  rounded
                  :multiple="true"
                >
                  <template v-slot:selection="{ item, index }">
                    <span v-if="index === 0">{{ item.name }} </span>
                    <span v-if="index === 1" class="grey--text text-caption">
                      (+{{ resetStatus.length - 1 }} )
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
              <v-btn
                class="btn"
                v-if="importSelected.length || redistributeOffice"
                @click.stop="drawer = !drawer"
                >Назначить на менеджеров</v-btn
              >
              <v-spacer></v-spacer>
            </v-row>
          </v-col>
          <v-col cols="6">
            <v-data-table
              :headers="import_headers"
              item-key="id"
              :items="filter_imports"
              ref="importtable"
              show-select
              :search="search"
              v-model="importSelected"
              @click:row="clickrow"
              :footer-props="{
                'items-per-page-options': [],
                'items-per-page-text': '',
              }"
            >
              <template v-slot:top="{ pagination, options, updateOptions }">
                <v-row>
                  <v-col
                    ><v-autocomplete
                      v-model="search"
                      :items="imports"
                      item-text="message"
                      item-value="message"
                      append-icon="mdi-magnify"
                      label="Поиск"
                      hide-details
                      outlined
                    ></v-autocomplete
                  ></v-col>
                  <v-col>
                    <!-- <v-data-footer
                      :pagination="pagination"
                      :options="options"
                      @update:options="updateOptions"
                      items-per-page-text=""
                  /> -->
                  </v-col>
                </v-row>
              </template>

              <template v-slot:item.start="{ item }">
                <div>{{ item.start.substring(0, 10) }}</div>
                <div>{{ item.start.substring(11) }}</div>
              </template>
              <template v-slot:item.provider="{ item }">
                <div>{{ item.provider }}</div>
                <div>{{ item.baer }}</div>
              </template>
              <template v-slot:item.message="{ item }">
                {{ item.message }}
                <v-icon small class="mr-2" @click.stop="editItem(item)">
                  mdi-pencil
                </v-icon>
              </template>
              <template v-slot:item.id="{ item }">
                <v-btn @click.stop="deleteImport(item)" plain
                  ><v-icon>mdi-delete</v-icon></v-btn
                >
              </template>
            </v-data-table>
          </v-col>
          <v-col cols="6" style="margin-top: 3.5rem">
            <v-data-table
              :headers="import_provider_headers"
              item-key="id"
              group-by="group"
              show-group-by
              :expanded.sync="expanded"
              :items="filter_importsProvLeads"
              ref="importprovtable"
              show-select
              v-model="importSelected"
              @click:row="clickrow"
              :footer-props="{
                'items-per-page-options': [],
                'items-per-page-text': '',
              }"
            >
              <template
                v-slot:group.header="{ group, headers, toggle, isOpen }"
              >
                <td :colspan="headers.length">
                  <v-btn
                    @click="toggle"
                    small
                    icon
                    :ref="group"
                    :data-open="isOpen"
                  >
                    <v-icon v-if="isOpen">mdi-chevron-up</v-icon>
                    <v-icon v-else>mdi-chevron-down</v-icon>
                  </v-btn>
                  {{ group }}
                </td>
              </template>
              <template v-slot:expanded-item="{ headers, item }">
                <td :colspan="headers.length">
                  <div class="row">
                    <div class="col">
                      <h6>Details</h6>
                      ... {{ item.name }}
                    </div>
                  </div>
                </td>
              </template>
              <template v-slot:item.sum="{ item }">
                {{ item.sum }}
                <v-icon small class="mr-2" @click.stop="editItem(item)">
                  mdi-pencil
                </v-icon>
              </template>
            </v-data-table>
          </v-col>
          <v-col cols="3"></v-col>
          <v-col cols="12" id="info_prov">
            {{ item.name }} {{ item.start }}
            <v-btn class="btn mx-1" @click="getHistory">История</v-btn>
            <span v-if="item.name">Пользователи: </span>
            <span v-for="usr in holdLidsUsers" :key="usr[0]">
              {{ usr[0] }}.
            </span>
          </v-col>
          <v-col cols="12" v-for="office in lidsByOffice" :key="office.name">
            <div class="d-flex align-center">
              <v-checkbox
                v-model="redistributeOffice"
                hide-details
                :label="office.name + ' - ' + office.lids.length"
                :value="office.name"
              ></v-checkbox>
            </div>
            <v-col cols="12" v-if="office.statuses">
              <div id="wrp_stat" class="wrp__statuses">
                <!-- <div

                v-for="u in usersStatuses"
                :key="u.user_id"
              >
                {{ u.user }} -->
                <template v-for="i in office.statuses">
                  <div class="status_wrp" :key="i.id">
                    <b
                      :style="{
                        background: i.color,
                        outline: '1px solid' + i.color,
                      }"
                      >{{ i.hm }}</b
                    >
                    <span>{{ i.name }}</span>
                  </div>
                </template>
                <!-- </div> -->
              </div>
            </v-col>
          </v-col>
          <v-col cols="12" v-if="Statuses">
            <div id="wrp_stat" class="wrp__statuses">
              <template v-for="i in Statuses">
                <div class="status_wrp" :key="i.id">
                  <b
                    :style="{
                      background: i.color,
                      outline: '1px solid' + i.color,
                    }"
                    >{{ i.hm }}</b
                  >
                  <span>{{ i.name }}</span>
                </div>
              </template>
            </div>
          </v-col>
          <v-col cols="12" v-if="historyStatus.length">
            <p>Переназначенные</p>
            <div id="wrp_stat" class="wrp__statuses">
              <template v-for="i in historyStatus">
                <div class="status_wrp" :key="i.id">
                  <b
                    :style="{
                      background: i.color,
                      outline: '1px solid' + i.color,
                    }"
                    >{{ i.hm }}</b
                  >
                  <span>{{ i.name }}</span>
                </div>
              </template>
              <!-- </div> -->
            </div>
          </v-col>
          <v-col cols="12">
            <div class="mt-3">
              <div
                class="wrp__statuses"
                v-for="u in history"
                :key="u.created_at"
              >
                {{ u.created_at }}
                <template v-for="i in JSON.parse(u.statuses)">
                  <div class="status_wrp" :key="i.id">
                    <b
                      :style="{
                        background: i.color,
                        outline: '1px solid' + i.color,
                      }"
                      >{{ i.hm }}</b
                    >
                    <span>{{ i.name }}</span>
                  </div>
                </template>
              </div>
            </div>
          </v-col>

          <v-col cols="12" v-if="leads.length">
            <div class="border pa-4">
              <v-data-table
                id="tabimplids"
                :headers="headers_leads"
                item-key="id"
                :items="filteredLeads"
                :footer-props="{
                  'items-per-page-options': [],
                  'items-per-page-text': '',
                }"
                :disable-items-per-page="true"
                :loading="loading"
                loading-text="Загружаю... Ожидайте"
                ref="datatablelids"
                @click:row="getLog"
                :single-expand="true"
              >
                <template v-slot:expanded-item="{ headers, item }">
                  <td :colspan="headers.length" class="blackborder">
                    <v-row>
                      <v-col cols="12">
                        <logtel :lid_id="item.id" :key="item.id" />
                      </v-col>
                    </v-row>
                  </td>
                </template>
                <template
                  v-slot:top="{ pagination, options, updateOptions }"
                  :footer-props="{
                    'items-per-page-options': [50, 10, 100, 250, 500, -1],
                    'items-per-page-text': '',
                  }"
                >
                  <v-row>
                    <!-- <v-spacer></v-spacer> -->
                    <v-col cols="3" class="mt-3">
                      <v-data-footer
                        :pagination="pagination"
                        :options="options"
                        @update:options="updateOptions"
                        :items-per-page-options="[50, 10, 100, 250, 500, -1]"
                        :items-per-page-text="''"
                      />
                    </v-col>
                    <v-col cols="3">
                      <v-select
                        label="Cтатус в файл"
                        ref="filterStatus"
                        color="red"
                        v-model="filterStatus"
                        :items="Statuses"
                        item-text="name"
                        item-value="id"
                        outlined
                        rounded
                        :multiple="true"
                      >
                        <template v-slot:selection="{ item, index }">
                          <span v-if="index === 0">{{ item.name }} </span>
                          <span
                            v-if="index === 1"
                            class="grey--text text-caption"
                          >
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
                      <div class="d-flex">
                        <v-select
                          label="Фильтр статус"
                          v-model="filterStatusTabl"
                          :items="Statuses"
                          item-text="name"
                          item-value="id"
                          outlined
                          rounded
                          :multiple="true"
                        >
                          <template v-slot:selection="{ item, index }">
                            <span v-if="index === 0">{{ item.name }} </span>
                            <span
                              v-if="index === 1"
                              class="grey--text text-caption"
                            >
                              (+{{ filterStatusTabl.length - 1 }} )
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
                        <v-select
                          label="Фильтр office"
                          v-model="filterOfficeTabl"
                          :items="lidsByOffice"
                          item-text="name"
                          item-value="name"
                          outlined
                          rounded
                          :multiple="true"
                        ></v-select>
                      </div>
                    </v-col>
                    <v-col cols="3">
                      <v-select
                        v-model="exportfields"
                        :items="exportfield"
                        label="Поля для експорта"
                        multiple
                        outlined
                      ></v-select
                    ></v-col>
                    <v-col cols="3">
                      <v-btn
                        outlined
                        rounded
                        @click="exportImportedXlsx"
                        class="border"
                      >
                        <v-icon left> mdi-file-excel </v-icon>
                        Скачать таблицу
                      </v-btn>
                    </v-col>
                  </v-row>
                </template>
              </v-data-table>
            </div>
          </v-col>
        </v-row>
      </v-tab-item>

      <v-tab-item v-if="$attrs.user.role_id == 1 && $attrs.user.group_id == 0">
        <importBTC></importBTC>
      </v-tab-item>
      <v-tab-item>
        <v-container>
          <v-row>
            <v-col>
              <v-radio-group v-model="email_tel" row>
                <v-radio label="email" value="email"></v-radio>
                <v-radio label="телефон" value="tel"></v-radio>
              </v-radio-group>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-textarea
                class="border pa-3"
                v-model="list_email"
                label="Почтовые адреса или телефоны "
              ></v-textarea>
            </v-col>
            <v-col cols="6">
              <v-file-input
                v-model="file_emails"
                label="загрузить txt"
                show-size
                truncate-length="24"
                @change="onFileChange"
              ></v-file-input>
              <v-select
                v-model="printfields"
                :items="printfield"
                label="Поля для отчёта"
                multiple
                outlined
              ></v-select>
              <v-btn @click="checkEmails" v-if="list_email" class="primary"
                >Проверить<v-progress-circular
                  v-if="loading"
                  indeterminate
                  color="amber"
                ></v-progress-circular
              ></v-btn>
            </v-col>
            <v-col cols="12" v-if="duplicate_leads.length">
              <v-btn outlined rounded @click="exportXlsx" class="border">
                <v-icon left> mdi-file-excel </v-icon>
                Скачать таблицу
              </v-btn>
              <v-data-table
                :headers="duplicate_leads_headers"
                item-key="id"
                :items="duplicate_leads"
                ref="duplicatetable"
              >
              </v-data-table>
            </v-col>
          </v-row>
        </v-container>
      </v-tab-item>
    </v-tabs-items>
    <ConfirmDlg ref="confirm" />
    <v-dialog v-model="dialog" max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">Редактировать сообщение</span>
        </v-card-title>

        <v-card-text>
          <v-container>
            <v-row>
              <v-col cols="12" v-if="editedItem.message">
                <v-text-field
                  v-model="editedItem.message"
                  label="Сообщение"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="editedItem.sum"
                  label="Сумма"
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-radio-group v-model="editedItem.cp" row>
                  <v-radio label="CPL" value="L"></v-radio>
                  <v-radio label="CPA" value="A"></v-radio>
                </v-radio-group>
              </v-col>
              <v-col cols="12" v-if="editedItem.message">
                <v-select
                  v-model="editedItem.provider_id"
                  :items="providers"
                  item-text="name"
                  item-value="id"
                  label="Провайдер"
                  @change="setBaer()"
                ></v-select>
                <v-select
                  v-if="baers.length"
                  v-model="editedItem.baer"
                  :items="baers"
                  label="Баер"
                  clearable
                ></v-select>
              </v-col>
            </v-row>
          </v-container>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" text @click="close"> Отмена </v-btn>
          <v-btn color="blue darken-1" text @click="save"> Сохранить </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-navigation-drawer width="450" v-model="drawer" fixed temporary right>
      <selectUsers :user="$attrs.user" @getUserIds="p_user_ids" />
    </v-navigation-drawer>
  </div>
</template>

<script>
import XLSX from "xlsx";
import axios from "axios";
import importBTC from "./importBTC";
import importxlsx from "./importxlsx";
import logtel from "../manager/logtel";
import selectUsers from "./UI/selectUsers";
import _ from "lodash";
export default {
  name: "ImportCSV",
  data: () => ({
    search: "",
    user_ids: [],
    clearable: true,
    takedates: true,
    dateFrom: false,
    dateTo: false,
    dateProps: { locale: "ru-RU", format: "24hr" },
    datetimeFrom: new Date(new Date().setDate(new Date().getDate() - 3))
      .toISOString()
      .substring(0, 10),
    datetimeTo: new Date().toISOString().substring(0, 10),
    filter_import_provider: [],
    i_providers: [],
    list_email: "",
    file_emails: [],
    in_db: [],
    out_db: [],
    message: "",
    snackbar: false,
    loading: false,
    userid: null,
    users: [],
    providers: [],
    statuses: [],
    imports: [],
    selectedStatus: 8,
    selectedProvider: 0,
    related_user: [],
    selected: [],
    importSelected: [],
    files: [],
    search: "",
    filtertel: "",
    headers_leads: [
      { text: "Имя", value: "name" },
      { text: "Email", value: "email" },
      { text: "Телефон.", align: "start", value: "tel" },
      // { text: "Афилятор", value: "afilyator" },
      // { text: "Поставщик", value: "provider" },
      { text: "Менеджер", value: "user" },
      // { text: "Создан", value: "date_created" },
      { text: "Статус", value: "status" },
    ],
    headers: [
      { text: "Имя", value: "name" },
      { text: "Email", value: "email" },
      { text: "Тел.", align: "start", value: "tel" },
      { text: "Афилятор", value: "afilyator" },
    ],
    import_headers: [
      { text: "Дата час початку", value: "start", sortable: false },
      // { text: "Дата час закінчення", value: "end" },
      { text: "Поставщик", value: "provider", sortable: false },
      // { text: "Баер", value: "baer" },
      // { text: "Хто імпортував", value: "user" },
      { text: "Сумма", value: "sum", sortable: false },
      { text: "L/A", value: "cp", sortable: false },
      { text: "Коментар", value: "message", sortable: false },
      { text: "NEW", value: "hmnew", sortable: false },
      { text: "CallBack", value: "hmcb", sortable: false },
      { text: "Deposit", value: "hmdp", sortable: false },
      { text: "Кол-во", value: "hm", sortable: false },
      { text: "", value: "id", sortable: false },
    ],
    import_provider_headers: [
      { text: "Дата", value: "start", sortable: false, groupable: false },
      {
        text: "Поставщик",
        value: "provider",
        sortable: false,
        groupable: false,
      },
      { text: "Сумма", value: "sum", sortable: false, groupable: false },
      { text: "L/A", value: "cp", sortable: false, groupable: false },
      { text: "NEW", value: "hmnew", sortable: false, groupable: false },
      { text: "CallBack", value: "hmcb", sortable: false, groupable: false },
      { text: "Deposit", value: "hmdp", sortable: false, groupable: false },
      { text: "Кол-во", value: "hm", sortable: false, groupable: false },
      { text: "GEO", value: "geo", sortable: false, groupable: false },
      { text: "group", value: "group", sortable: false },
      // { text: "", value: "id" },
    ],
    duplicate_leads_headers: [
      { text: "Афилятор", value: "afilyator" },
      { text: "Емаил", value: "email" },
      { text: "Имя", value: "name" },
      { text: "Оффис", value: "office_name" },
      { text: "Провайдер", value: "provider_name" },
      { text: "Статус", value: "status_name" },
      { text: "Тел", value: "tel" },
      { text: "Оператор", value: "user_name" },
      { text: "Создан", value: "created" },
    ],
    importsProvLeads: [],
    duplicate_leads: [],
    parse_header: [],
    parse_csv: [],
    sortOrders: {},
    sortKey: "tel",
    tab: 0,
    Statuses: [],
    filterStatus: [],
    filterStatusTabl: [],
    filterOfficeTabl: [],
    resetStatus: [],
    leads: [],
    email_tel: "email",
    printfield: [
      "tel",
      "name",
      "created",
      "updated",
      "status_id",
      "status_name",
      "email",
      "user_name",
      "provider_name",
      "afilyator",
      "office_name",
    ],
    printfields: [],
    exportfield: [
      "name",
      "email",
      "tel",
      "status",
      "date_created",
      "updated_at",
      "user",
      "provider",
      "afilyator",
      "office",
    ],
    exportfields: [
      "name",
      "email",
      "tel",
      "status",
      "date_created",
      "updated_at",
      "user",
      "provider",
      "afilyator",
      "office",
    ],
    item: {},
    dialog: false,
    editedItem: {
      id: 0,
      // start:'',
      // end:'',
      provider: "",
      baer: "",
      // provider_id:'',
      // user:'',
      message: "",
      // group_id:'',
      // load_key:'',
      sum: 0,
      cp: "L",
    },
    usersStatuses: [],
    drawer: false,
    history: [],
    historyStatus: [],
    holdLidsUsers: [],
    baers: [],
    lidsByOffice: [],
    offices: [],
    redistributeOffice: null,
    expanded: [],
  }),
  watch: {
    selectedProvider: function (newval) {
      this.users = [];
      this.related_user = [];

      const rel_user = this.providers.find(
        (p) => p.id == newval
      ).related_users_id;
      if (rel_user.length > 2) {
        this.related_user = JSON.parse(rel_user);
        this.getUsers();
      }
    },
  },

  mounted() {
    this.getImports();
    //this.ImportedProvLids();
    this.getProviders();
    this.getOffices();
    this.getStatuses();
  },
  computed: {
    filteredLeads() {
      return this.leads.filter((i) => {
        return (
          (!this.filterStatusTabl.length ||
            this.filterStatusTabl.includes(i.status_id)) &&
          (!this.filterOfficeTabl.length ||
            this.filterOfficeTabl.includes(i.office))
        );
      });
    },
    filteredItems() {
      let reg = new RegExp("^" + this.filtertel);
      return this.parse_csv.filter((i) => {
        return !this.filtertel || reg.test(i.tel);
      });
    },
    filter_imports() {
      return this.imports.filter((i) => {
        return (
          this.filter_import_provider.length == 0 ||
          this.filter_import_provider.includes(i.provider_id)
        );
      });
    },
    filter_importsProvLeads() {
      if (this.importsProvLeads.length) {
        setTimeout(() => {
          // wait and then close all groups
          this.closeAll();
        }, 800);
        return this.importsProvLeads.filter((i) => {
          return (
            this.filter_import_provider.length == 0 ||
            this.filter_import_provider.includes(i.provider_id)
          );
        });
      }
    },
  },
  methods: {
    closeAll() {
      Object.keys(this.$refs).forEach((k) => {
        //console.log(this.$refs[k]);
        if (this.$refs[k] && this.$refs[k].$attrs["data-open"]) {
          this.$refs[k].$el.click();
        }
      });
    },
    setBaer() {
      const baer = this.providers.find((p) => {
        return p.id == this.editedItem.provider_id;
      }).baer;
      this.baers = [];
      if (baer && baer != "") {
        this.baers = baer.split(";");
      }
    },
    getHistory() {
      const self = this;
      self.loading = true;
      let data = {};
      data.id = self.item.id;
      data.message = self.item.message;
      axios
        .post("api/getHistory", data)
        .then(function (response) {
          self.loading = false;
          self.history = response.data.history;
          self.historyStatus = response.data.statuses;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    redistributeLids() {
      const self = this;
      let data = {};
      self.loading = true;
      data.lid_ids = this.lidsByOffice
        .find((f) => {
          return f.name == self.redistributeOffice;
        })
        .lids.filter((f) => {
          return (
            !this.resetStatus.length || this.resetStatus.includes(f.status_id)
          );
        })
        .map((l) => l.id);
      data.usersIds = this.user_ids;
      data.resetStatus = this.resetStatus;
      data.id = this.item.id;
      data.provider_id = this.item.provider_id;
      data.message = this.item.message;
      data.start = this.item.start;
      data.geo = this.item.geo;
      if (this.item.end != undefined) {
        data.end = this.item.end;
      }
      axios
        .post("api/redistributeLids", data)
        .then(function (response) {
          self.message = `Переназначение успешно выполнено<br>Для Офиса: ${self.redistributeOffice}`;
          self.loading = false;
          self.importSelected = [];
          self.lidsByOffice = [];

          (self.redistributeOffice = null), (self.user_ids = []);
          self.snackbar = true;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    p_user_ids(user_ids) {
      const self = this;
      this.user_ids = user_ids;
      if (this.user_ids.length == 0) {
        this.user_ids = [this.$attrs.user.id];
      }
      this.drawer = false;
      if (this.redistributeOffice) {
        this.redistributeLids();
        return;
      }
      self.loading = true;

      let data = {};
      data.importsIdsm = this.importSelected.map(
        ({ id, message, provider_id, start, geo }) => ({
          id,
          message,
          provider_id,
          start,
          geo,
        })
      );
      data.usersIds = this.user_ids;
      data.resetStatus = this.resetStatus;

      axios
        .post("api/redistribute", data)
        .then(function (response) {
          self.message = `Переназначение успешно выполнено<br>Для статусов: ${self.statuses
            .filter((s) => {
              return data.resetStatus.includes(s.id);
            })
            .map(({ name }) => {
              return name;
            })
            .toString()}<br>Файлы: ${self.importSelected.map(({ message }) => {
            return message;
          })}`;
          self.loading = false;
          self.importSelected = [];

          self.lidsByOffice = [];

          (self.redistributeOffice = null), (self.user_ids = []);
          self.snackbar = true;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    ImportedProvLids() {
      const self = this;
      let datefrom = self.datetimeFrom,
        dateto = self.datetimeTo;
      if (self.takedates == 0) {
        datefrom = 0;
        dateto = 0;
      }

      axios
        .get("api/ImportedProvLids/" + datefrom + "/" + dateto)
        .then(function (response) {
          self.importsProvLeads = response.data;

          if (self.importsProvLeads) {
            self.importsProvLeads = self.importsProvLeads.map((ip) => {
              ip.group = ip.date + " " + ip.provider;
              return ip;
            });
            let a_prov = _.uniq(
              _.map(self.importsProvLeads, (el) => {
                return el.provider_id;
              })
            );
            self.i_providers = _.union(
              self.i_providers,
              self.providers.filter((i) => {
                return a_prov.includes(i.id);
              })
            );
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    async deleteImport(item) {
      const self = this;
      if (
        await this.$refs.confirm.open(
          "Видалити???",
          "Усі імпортовані ліди за вказаним записом будуть видалені безповоротно. Видаляємо?"
        )
      ) {
        axios
          .post("api/deleteImportedLids", item)
          .then(function (response) {
            self.getImports();
          })
          .catch(function (error) {
            console.log(error);
          });
      }
    },
    exportImportedXlsx() {
      const self = this;
      let flids;
      if (self.filterStatus.length > 0) {
        flids = _.filter(self.leads, (el) => {
          return self.filterStatus.includes(el.status_id);
          //"created", "updated","user_name", "provider_name", "afilyator", "office_name"
        }).map(
          ({
            name,
            email,
            tel,
            status,
            date_created,
            updated_at,
            user,
            provider,
            afilyator,
            office,
          }) => ({
            name,
            email,
            tel,
            status,
            date_created,
            updated_at,
            user,
            provider,
            afilyator,
            office,
          })
        );
      } else {
        flids = self.leads.map(
          ({
            name,
            email,
            tel,
            status,
            date_created,
            updated_at,
            user,
            provider,
            afilyator,
            office,
          }) => ({
            name,
            email,
            tel,
            status,
            date_created,
            updated_at,
            user,
            provider,
            afilyator,
            office,
          })
        );
      }

      const newLeads = flids.map((obj) => {
        const newObj = {};
        self.exportfields.forEach((prop) => {
          newObj[prop] = obj[prop];
        });
        return newObj;
      });

      var wb = XLSX.utils.book_new(); // make Workbook of Excel

      window["list"] = XLSX.utils.json_to_sheet(newLeads);
      XLSX.utils.book_append_sheet(wb, window["list"], "imported leads");

      // export Excel file
      XLSX.writeFile(
        wb,
        "imported_" + self.item.start.replaceAll(/ |:/g, "_") + ".xlsx"
      );
    },
    exportXlsx() {
      const self = this;
      const obj = _.groupBy(self.filteredItems, "status");
      const lidsByStatus = Array.from(Object.keys(obj), (k) => [
        `${k}`,
        obj[k],
      ]);

      var wb = XLSX.utils.book_new(); // make Workbook of Excel
      const newLeads = self.duplicate_leads.map((obj) => {
        const newObj = {};
        self.printfields.forEach((prop) => {
          newObj[prop] = obj[prop];
        });
        return newObj;
      });
      window["list"] = XLSX.utils.json_to_sheet(newLeads);
      XLSX.utils.book_append_sheet(wb, window["list"], "duplicate_emailes");
      const unique = self.out_db.map((i) => ({ email: i }));
      window["unique"] = XLSX.utils.json_to_sheet(unique);
      XLSX.utils.book_append_sheet(wb, window["unique"], "unique");

      // export Excel file
      XLSX.writeFile(
        wb,
        "dupl_" + self.email_tel + new Date().toDateString() + ".xlsx"
      ); // name of the file is 'book.xlsx'
    },
    checkEmails() {
      let vm = this;
      vm.loading = true;
      vm.snackbar = false;
      vm.message = "";
      vm.duplicate_leads = [];
      vm.in_db = [];
      vm.out_db = [];
      let data = {};
      data.emails = vm.list_email
        .replace(/[\r]/gm, "")
        .replaceAll(" ", "")
        .split("\n");
      data.check = 1;
      data.email_tel = vm.email_tel;
      axios
        .post("api/checkEmails", data)
        .then(function (res) {
          vm.in_db = res.data.emails.filter((n) => n);

          vm.out_db = [
            ...new Set(
              data.emails.filter((i) => !vm.in_db.includes(i.toLowerCase()))
            ),
          ];
          vm.message =
            "Уникальных: " +
            vm.out_db.length +
            "<br>Дубликатов: " +
            vm.in_db.length;
          vm.snackbar = true;
          vm.duplicate_leads = res.data.leads;

          vm.loading = false;
          vm.list_email = "";
          vm.files = [];
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    filterStatuses() {
      const self = this;
      let stord = this.leads;
      let groupByOffice = [];
      self.Statuses = [];
      self.lidsByOffice = [];
      groupByOffice = Object.entries(_.groupBy(this.leads, "office_id"));
      groupByOffice.forEach((a_office) => {
        const nameoffice = self.offices.find((o) => {
          return o.id == a_office[0];
        }).name;
        const lids = Object.entries(_.groupBy(a_office[1], "status"));
        let statuses = [];
        lids.map(function (i) {
          //i[0]//name
          //i[1]//array
          let el = self.statuses.find((s) => s.name == i[0]);
          statuses.push({
            id: el.id,
            name: i[0],
            hm: i[1].length,
            order: el.order,
            color: el.color,
          });
        });
        statuses = _.orderBy(statuses, "order");
        self.lidsByOffice.push({
          name: nameoffice,
          lids: a_office[1],
          statuses: statuses,
        });
      });
      self.holdLidsUsers = Object.entries(_.groupBy(self.leads, "user"));
      stord = Object.entries(_.groupBy(stord, "status"));
      stord.map(function (i) {
        //i[0]//name
        //i[1]//array
        let el = self.statuses.find((s) => s.name == i[0]);
        self.Statuses.push({
          id: el.id,
          name: i[0],
          hm: i[1].length,
          order: el.order,
          color: el.color,
        });
      });
      self.Statuses = _.orderBy(self.Statuses, "order");
    },
    usercolor(user) {
      return user.role_id == 2 ? "green" : "blue";
    },
    putSelectedLidsDB() {
      let self = this;
      self.loading = true;
      let send = {};
      send.user_id = this.userid;
      send.message = this.message;
      send.provider_id = this.selectedProvider;
      if (this.selectedStatus !== 0) {
        send.status_id = this.selectedStatus;
      }
      if (this.$refs.datatable.items.length > 0) {
        if (this.selected.legth) send.data = this.selected;
        else send.data = this.$refs.datatable.items;
        axios
          .post("api/Lid/newlids", send)
          .then(function (response) {
            // console.log(response);
            if (self.selected.length) {
              self.parse_csv = self.parse_csv.filter(
                (ar) => !self.selected.find((rm) => rm.tel === ar.tel)
              );
            } else {
              self.parse_csv = [];
            }
            self.selected = [];
            self.getUsers();
            self.loading = false;
            self.files = [];
            // save to imports db
            //======================
            let info = {};

            info.start = response.data.date_start
              .substring(0, 19)
              .replace("T", " ");
            info.end = response.data.date_end
              .substring(0, 19)
              .replace("T", " ");
            info.provider_id = self.selectedProvider;
            info.user_id = self.$attrs.user.id;
            info.message = self.message;

            axios
              .post("api/imports", info)
              .then(function (response) {})
              .catch(function (error) {
                console.log(error);
              });
          })
          .catch(function (error) {
            console.log(error);
          });
      } else if (
        (this.search !== "" || this.filtertel !== "") &&
        this.$refs.datatable.$children[0].filteredItems.length > 0
      ) {
        send.data = this.$refs.datatable.$children[0].filteredItems;
        axios
          .post("api/Lid/newlids", send)
          .then(function (response) {
            self.parse_csv = self.parse_csv.filter(
              (ar) =>
                !self.$refs.datatable.$children[0].filteredItems.find(
                  (rm) => rm.tel === ar.tel
                )
            );
            // self.getUsers();
            self.search = "";
            self.filtertel = "";
            self.loading = false;
            // save to imports db
            //======================
            let info = {};

            info.start = response.data.date_start
              .substring(0, 19)
              .replace("T", " ");
            info.end = response.data.date_end
              .substring(0, 19)
              .replace("T", " ");
            info.provider_id = self.selectedProvider;
            info.user_id = self.$attrs.user.id;
            info.message = self.message;

            axios
              .post("api/imports", info)
              .then(function (response) {
                // console.log(response);
              })
              .catch(function (error) {
                console.log(error);
              });
          })
          .catch(function (error) {
            console.log(error);
          });
      }
      if (this.parse_csv.length == 0) {
        this.files = [];
        this.selectedProvider = "";
      }
      this.userid = null;
      this.$refs.radiogroup.lazyValue = null;
      this.getUsers();
    },

    getLog(i, row) {
      this.$refs.datatablelids.expansion = {};

      row.expand(!row.isExpanded);
    },
    clickrow(item) {
      let self = this;
      let data = {};
      self.leads = [];
      self.Statuses = [];
      self.historyStatus = [];
      self.usersStatuses = [];
      self.history = [];
      self.loading = true;
      self.item = item;
      self.item.name = self.providers.find(
        (s) => s.id == item.provider_id
      ).name;
      data.provider_id = item.provider_id;
      data.message = item.message;
      data.start = item.start;
      data.geo = item.geo;
      if (item.end != undefined) {
        data.end = item.end;
      }

      axios
        .post("api/getlidsImportedProvider", data)
        .then(function (response) {
          self.leads = response.data;
          self.leads.map(function (e) {
            e.date_created = e.created_at.substring(0, 10);
            if (e.status_id)
              e.status = self.statuses.find((s) => s.id == e.status_id).name;
            if (e.provider_id)
              e.provider = self.providers.find(
                (s) => s.id == e.provider_id
              ).name;
          });
          self.filterStatuses();
          self.loading = false;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    getOffices() {
      let self = this;
      self.filterOffices = self.$attrs.user.office_id;
      axios
        .get("/api/getOffices")
        .then((res) => {
          self.offices = res.data;
          // if (self.$attrs.user.role_id == 1) {
          //   self.offices.unshift({ id: 0, name: "--выбор--" });
          //   self.filterOffices = self.offices[1].id;
          // }
          if (self.$attrs.user.office_id > 0) {
            self.offices = self.offices.filter(
              (o) => o.id == self.$attrs.user.office_id
            );
          }
        })
        .catch((error) => console.log(error));
    },
    getProviders() {
      let self = this;
      axios
        .get("/api/provider")
        .then((res) => {
          self.providers = res.data.map(
            ({ name, id, related_users_id, baer }) => ({
              name,
              id,
              related_users_id,
              baer,
            })
          );
        })
        .catch((error) => console.log(error));
    },
    getImports() {
      let self = this;
      self.loading = true;
      let datefrom = self.datetimeFrom,
        dateto = self.datetimeTo;
      if (self.takedates == 0) {
        datefrom = 0;
        dateto = 0;
      }
      axios
        .get("/api/imports/" + datefrom + "/" + dateto)
        .then((res) => {
          self.imports = res.data.map(
            ({
              id,
              start,
              end,
              provider,
              provider_id,
              user,
              message,
              group_id,
              load_key,
              sum,
              cp,
              baer,
              hmnew,
              hm,
              hmcb,
              hmdp,
            }) => ({
              id,
              start,
              end,
              provider,
              provider_id,
              user,
              message,
              group_id,
              load_key,
              sum,
              cp,
              baer,
              hmnew,
              hm,
              hmcb,
              hmdp,
            })
          );
          let a_prov = _.uniq(
            _.map(self.imports, (el) => {
              return el.provider_id;
            })
          );
          self.i_providers = self.providers.filter((i) => {
            return a_prov.includes(i.id);
          });

          if (self.$attrs.user.group_id > 0) {
            self.imports = self.imports.filter(
              (i) => i.group_id == self.$attrs.user.group_id
            );
          }
          self.ImportedProvLids();
          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data.map(({ name, id, role_id, fio }) => ({
            name,
            id,
            role_id,
            fio,
          }));
        })
        .catch((error) => console.log(error));
    },
    getUsers_del() {
      let self = this;

      axios
        .post("/api/getusers", self.related_user)
        .then((res) => {
          self.users = res.data.map(({ name, id, role_id, fio, hmlids }) => ({
            name,
            id,
            role_id,
            fio,
            hmlids,
          }));
        })
        .catch((error) => console.log(error));
    },
    editItem(item) {
      this.editedItem = Object.assign({}, item);
      if (item.message != undefined) {
        this.editedIndex = this.imports.indexOf(item);
        this.setBaer();
      } else {
        this.editedIndex = this.importsProvLeads.indexOf(item);
      }
      this.dialog = true;
    },
    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },
    save() {
      if (this.editedIndex > -1) {
        if (this.editedItem.message != undefined) {
          this.saveLoads(this.editedItem);
          Object.assign(this.imports[this.editedIndex], this.editedItem);
        } else {
          this.saveLoads(this.editedItem);
          Object.assign(
            this.importsProvLeads[this.editedIndex],
            this.editedItem
          );
        }
      }
      this.close();
    },
    saveLoads(u) {
      var form_data = new FormData();

      for (var key in u) {
        form_data.append(key, u[key]);
      }
      axios
        .post("/api/importUpdate", form_data)
        .then((res) => {})
        .catch((error) => console.log(error));
    },
    getStatuses() {
      let self = this;
      axios
        .get("/api/statuses")
        .then((res) => {
          self.statuses = res.data.map(({ uname, name, id, color, order }) => ({
            uname,
            name,
            id,
            color,
            order,
          }));
        })
        .catch((error) => console.log(error));
    },
    onFileChange(f) {
      if (f == null) return;
      const ftype = [
        "text/comma-separated-values",
        "text/csv",
        "application/csv",
        "text/anytext",
        "text/plain",

        "application/excel",
        "application/xls",
        "application/x-xls",
        "application/vnd-xls",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-excel",
        "application/vnd.ms-excel",
        "application/msexcel",
        "application/x-msexcel",
        "application/x-ms-excel",
        "application/x-excel",
        "application/x-dos_ms_excel",
      ];
      if (ftype.indexOf(f.type) >= 0) {
        this.createInput(f);
      } else {
        this.files = [];
      }
    },
    txt2Array(txt) {
      let vm = this;
      vm.list_email = txt;
    },
    csvJSON(csv) {
      var vm = this;
      var lines = csv.split("\n");
      var result = [];
      var headers = lines[0].split(";");
      headers = ["name", "email", "tel", "afilyator"];
      // vm.parse_header = lines[0].split(",");
      // lines[0].split(",").forEach(function (key) {
      //   vm.sortOrders[key] = 1;
      // });

      lines.map(function (line, indexLine) {
        if (indexLine < 1) return; // Jump header line

        var obj = {};
        line = line.trim();
        var currentline = line.split(";");

        headers.map(function (header, indexHeader) {
          obj[header] = currentline[indexHeader];
        });

        result.push(obj);
      });

      result.pop(); // remove the last item because undefined values

      return result; // JavaScript object
    },
    fixdata(data) {
      var o = "",
        l = 0,
        w = 10240;
      for (; l < data.byteLength / w; ++l)
        o += String.fromCharCode.apply(
          null,
          new Uint8Array(data.slice(l * w, l * w + w))
        );
      o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
      return o;
    },
    createInput(file) {
      let ext = file.name.slice(
        (Math.max(0, file.name.lastIndexOf(".")) || Infinity) + 1
      );

      let promise = new Promise((resolve, reject) => {
        var reader = new FileReader();
        var vm = this;
        reader.onload = (e) => {
          resolve((vm.fileinput = reader.result));
        };
        if (ext == "txt" || ext == "csv") {
          reader.readAsText(file);
        } else {
          reader.readAsBinaryString(file);
        }
      });

      promise.then(
        (result) => {
          let vm = this;
          if (ext == "txt" || ext == "csv") {
            vm.parse_txt_emails = vm.txt2Array(vm.fileinput);
          } else {
            var workbook = XLSX.read(result, { type: "binary" }),
              firstSheetName = workbook.SheetNames[0],
              worksheet = workbook.Sheets[firstSheetName];

            vm.list_email = XLSX.utils.sheet_to_csv(worksheet, {
              header: 1,
            });
          }
        },
        (error) => {
          /* handle an error */
          console.log(error);
        }
      );
    },
  },
  components: {
    importBTC,
    importxlsx,
    logtel,
    ConfirmDlg: () => import("./ConfirmDlg"),
    selectUsers,
  },
};
</script>

<style>
.csv .v-data-table > .v-data-table__wrapper > table > tbody > tr > td {
  height: 4rem;
}
.csv .v-data-footer {
  justify-content: end;
}
</style>
