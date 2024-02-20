<template>
  <v-row no-gutters>
    <v-col>
      <v-data-table
        v-bind:headers="headers"
        :options.sync="pagination"
        :items="items"
        :server-items-length="totalItems"
        class="elevation-1 text-uppercase font-weight-bold caption ma-2"
        fixed-header
        dense
        @click:row="
          (row) => {
            if (Object.keys(row).length > 0 && itemRow?.id != row.id) {
              show = true;
              itemRow = row;
            } else {
              show = false;
              itemRow = {};
            }
          }
        "
        height="400"
      >
        <template #top>
          <search-panel
            :rightDrawer="rightDrawer"
            @cancelSearch="cancelSearch"
            @resetFilter="resetFilter"
          >
            <v-form ref="formFilter">
              <v-row class="mr-2 offset-1 overline" dense>
                <v-text-field
                  v-model="filters.full_name"
                  label="Buscar Nombre:"
                  prepend-icon="mdi-magnify"
                  outlined
                  clearable
                  dense
                ></v-text-field>
                <v-text-field
                  v-model="filters.phone"
                  label="Buscar telefono:"
                  prepend-icon="mdi-magnify"
                  outlined
                  clearable
                  dense
                ></v-text-field>

                <v-select
                  v-model="filters.capacidad_tech"
                  :items="['Baja', 'Mediana', 'Alta', 'Experto']"
                  label="Capacidad de Tecnologia"
                  outlined
                  filled
                  dense
                  multiple
                  chips
                  deletable-chips
                  clearable
                ></v-select>
                <v-select
                  v-model="filters.rating"
                  :items="['AAA', 'AA', 'A', 'Lista Negra']"
                  label="Calificacion"
                  outlined
                  filled
                  dense
                  multiple
                  chips
                  deletable-chips
                  clearable
                ></v-select>
                <v-select
                  v-model="filters.tactica_jd"
                  :items="[
                    'XPERIMENTAR',
                    'CONOCIENDO EL TERRITORIO',
                    'LIDERAZGO SOSTENIDO',
                    'RELACIONAMIENTO',
                    'EMBAJADORES',
                  ]"
                  label="Tácticas John Deere"
                  outlined
                  filled
                  dense
                  multiple
                  chips
                  deletable-chips
                  clearable
                ></v-select>
                <v-combobox
                  v-model="filters.cultivos"
                  label="Cultivos"
                  :items="options.cultivos"
                  multiple
                  chips
                  deletable-chips
                  outlined
                  hint="Lo que usualmente cultiva"
                  persistent-hint
                  clearable
                ></v-combobox>
              </v-row>
            </v-form>
          </search-panel>
          <v-card flat class="py-4 px-2 d-flex flex-row align-center">
            <v-text-field
              v-model="filters.search"
              prepend-icon="mdi-magnify"
              label="Fitrar por Nombre / Telefono"
              hide-details
              clearable
              outlined
              filled
              dense
            ></v-text-field>
            <v-spacer></v-spacer>
            <table-header-buttons
              :updateSearchPanel="updateSearchPanel"
              :reloadTable="loadProspects"
            ></table-header-buttons>
            <!-- @click="$router.push({ name: 'prospect.create' })" -->
          </v-card>
          <v-card>
            <v-card-title>
              Prospectos
              <v-spacer />
              <v-btn class="primary" @click="dialogCreate = true">
                Registrar Prospecto
                <v-icon right>mdi-plus</v-icon>
              </v-btn>
              <!-- <v-btn class="accent ml-2" @click="loadProspects()">
              <v-icon>mdi-refresh</v-icon>
            </v-btn> -->
            </v-card-title>
          </v-card>
          <dialog-component
            :show="dialogEdit"
            @close="dialogEdit = false"
            closeable
            :fullscreen="$vuetify.breakpoint.mobile"
            title="Editar Prospecto"
            :maxWidth="600"
          >
            <prospect-edit
              :propProspectId="editedId"
              :key="editedId"
            ></prospect-edit>
          </dialog-component>
          <dialog-component
            :show="dialogCreate"
            @close="dialogCreate = false"
            closeable
            :fullscreen="$vuetify.breakpoint.mobile"
            title="Registrar Nuevo Prospecto"
            :maxWidth="600"
          >
            <prospect-create></prospect-create>
          </dialog-component>
        </template>
        <template #[`item.phone`]="{ value }">
          {{ value | VMask("(###) ###-####") }}
        </template>
        <template #[`item.action`]="{ item }">
          <v-menu offset-x>
            <template #activator="{ on, attrs }">
              <v-btn icon small v-bind="attrs" v-on="on">
                <v-icon>mdi-dots-vertical</v-icon>
              </v-btn>
            </template>
            <v-list shaped>
              <v-list-item-group>
                <v-list-item @click="(editedId = item.id), (dialogEdit = true)">
                  <v-list-item-icon>
                    <v-icon class="blue--text" left>mdi-pencil</v-icon>
                  </v-list-item-icon>
                  <v-list-item-content>
                    <v-list-item-title>Editar Prospecto</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-list-item-group>
            </v-list>
          </v-menu>
        </template>
      </v-data-table>
    </v-col>
    <v-scroll-x-transition mode="out-in">
      <v-col v-if="show" cols="12" md="3">
        <v-card style="height: 100%" class="overflow-auto" dark>
          <v-toolbar dense flat class="pt-4">
            <v-toolbar-title class="text-wrap text-uppercase">
              {{ itemRow?.full_name }}
            </v-toolbar-title>
            <v-spacer />
            <v-btn icon @click="(show = false), (itemRow = {})">
              <v-icon color="red">mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
          <v-card-text>
            <v-list two-line dense>
              <v-list-item>
                <v-list-item-icon>
                  <v-icon color="indigo"> mdi-phone </v-icon>
                </v-list-item-icon>

                <v-list-item-content>
                  <v-list-item-title>{{
                    itemRow?.phone | VMask("(###) ###-####")
                  }}</v-list-item-title>
                  <v-list-item-subtitle>Telefono</v-list-item-subtitle>
                </v-list-item-content>

                <v-list-item-icon>
                  <v-icon>mdi-message-text</v-icon>
                </v-list-item-icon>
              </v-list-item>

              <v-divider inset></v-divider>

              <v-list-item v-if="!!itemRow?.email">
                <v-list-item-icon>
                  <v-icon color="indigo"> mdi-email </v-icon>
                </v-list-item-icon>

                <v-list-item-content>
                  <v-list-item-title>{{ itemRow?.email }}</v-list-item-title>
                  <v-list-item-subtitle>Personal</v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>

              <v-divider inset></v-divider>

              <v-list-item>
                <v-list-item-icon>
                  <v-icon color="indigo"> mdi-map-marker </v-icon>
                </v-list-item-icon>

                <v-list-item-content>
                  <v-list-item-title>{{ itemRow?.town }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ itemRow?.township?.estate?.name }},
                    {{ itemRow?.township?.name }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-card-text>
          <v-card-text class="overflow-auto text-uppercase">
            <v-subheader>Cultivos: </v-subheader>
            <v-chip-group active-class="primary--text" column>
              <v-chip v-for="tag in cultivos" :key="tag">
                {{ tag }}
              </v-chip>
            </v-chip-group>
            <v-divider class="my-2" />
            <v-subheader>Etiquetas: </v-subheader>
            <v-chip-group active-class="white--text" column>
              <v-chip color="pink" v-if="!!itemRow?.segmentacion" label>
                <strong> {{ itemRow?.segmentacion }}</strong> &nbsp;
                <span>(Segmentacion)</span>
              </v-chip>
              <v-chip color="orange" v-if="!!itemRow?.capacidad_tech" label>
                <strong> {{ itemRow?.capacidad_tech }}</strong> &nbsp;
                <span>(Cap. Tech)</span>
              </v-chip>
              <v-chip color="blue" v-if="!!itemRow?.rating" label>
                <strong> {{ itemRow?.rating }}</strong> &nbsp;
                <span>(Calif. Credito)</span>
              </v-chip>
              <v-chip color="green" v-if="!!itemRow?.tactica_jd" label>
                <strong> {{ itemRow?.tactica_jd }}</strong> &nbsp;
                <span>(Tactica JD)</span>
              </v-chip>
            </v-chip-group>
          </v-card-text>
        </v-card>
      </v-col>
    </v-scroll-x-transition>
  </v-row>
</template>

<script>
import DialogComponent from "../../components/DialogComponent.vue";
import ProspectCreate from "./ProspectCreate.vue";
import ProspectEdit from "./ProspectEdit.vue";
import SearchPanel from "@admin/components/shared/SearchPanel.vue";
import TableHeaderButtons from "@admin/components/shared/TableHeaderButtons.vue";
export default {
  components: {
    DialogComponent,
    ProspectCreate,
    ProspectEdit,
    SearchPanel,
    TableHeaderButtons,
  },
  data() {
    return {
      show: false,
      itemRow: {},
      showSearchPanel: false,
      dialogCreate: false,
      dialogEdit: false,
      headers: [
        { text: "Action", value: "action", align: "left", sortable: false },
        {
          text: "Dirigido a / Razon Social",
          value: "full_name",
          align: "left",
          sortable: false,
        },
        {
          text: "Organizacion / Cliente",
          value: "customer.full_name",
          align: "left",
          sortable: false,
        },
        { text: "Telefono", value: "phone", align: "left", sortable: false },
        {
          text: "Seguimientos Activos:",
          value: "tracking_count",
          align: "center",
          sortable: false,
        },
      ],
      items: [],
      totalItems: 0,
      pagination: {
        rowsPerPage: 10,
      },
      editedId: null,
      options: {
        customers: [],
        cultivos: [
          "Ajo",
          "Alfalfa",
          "Apio",
          "Avena",
          "Brócoli",
          "Calabaza",
          "Cebada",
          "Cebada",
          "Cebolla",
          "Chile Poblano",
          "Cilantro",
          "Col",
          "Coliflor",
          "Esparrago",
          "Espinaca",
          "Fresa",
          "Frijol",
          "Lechuga",
          "Maiz",
          "Papa",
          "Sorgo",
          "Trigo",
          "Zanahoria",
          "Zanahoria",
          "Zarzamora",
          "Jicama",
          "Hortalizas",
          "Fresa",
          "Jitomate",
          "Chile Jalapeño",
          "Chile Chilaca",
          "Tomate",
        ],
      },

      filters: {
        search: "",
        full_name: "",
        phone: "",
        segmentacion: [],
        cultivos: [],
        rating: [],
        tactica_jd: [],
        capacidad_tech: [],
        township: [],
      },
    };
  },
  computed: {
    rightDrawer: {
      get() {
        return this.showSearchPanel;
      },
      set(_showSearchPanel) {
        this.showSearchPanel = _showSearchPanel;
      },
    },
    cultivos() {
      return this.itemRow?.meta_data?.cultivos ?? [];
    },
  },
  mounted() {
    const self = this;
    self.$store.commit("setBreadcrumbs", [{ label: "Prospectos", name: "" }]);
    this.getOptions();
  },
  watch: {
    pagination: {
      handler: _.debounce(function (v) {
        this.loadProspects(() => {});
      }, 700),
      deep: true,
    },
    filters: {
      handler: _.debounce(function (v) {
        this.loadProspects(() => {});
      }, 700),
      deep: true,
    },
  },
  methods: {
    updateSearchPanel() {
      this.rightDrawer = !this.rightDrawer;
    },
    cancelSearch() {
      this.showSearchPanel = false;
    },
    resetFilter() {
      const _this = this;
      _this.$refs.formFilter.reset();
      _this.pagination.itemsPerPage = 10;
      _this.pagination.page = 1;
    },
    async loadProspects(cb) {
      const _this = this;

      let params = {
        ..._this.filters,
        cultivos: _this.filters?.cultivos.join(","),
        segmentacion: _this.filters?.segmentacion.join(","),
        rating: _this.filters?.rating.join(","),
        tactica_jd: _this.filters?.tactica_jd.join(","),
        capacidad_tech: _this.filters?.capacidad_tech.join(","),
        order_sort: _this.pagination.sortDesc[0] ? "asc" : "desc",
        order_by: _this.pagination.sortBy[0] || "id",
        page: _this.pagination.page,
        per_page: _this.pagination.itemsPerPage,
      };

      await axios.get("/admin/prospects", { params }).then((response) => {
        _this.items = response.data.data.data;
        _this.totalItems = response.data.data.total;
        _this.pagination.totalItems = response.data.data.total;
        (cb || Function)();
      });
    },
    async getOptions() {
      let res = await axios.get("/admin/options/prospects");
      this.options.customers = res.data.data.options.customers;
    },
  },
};
</script>
