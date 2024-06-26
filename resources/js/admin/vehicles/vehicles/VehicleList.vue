<template>
  <v-data-table
    :headers="headers"
    :items="items"
    :options.sync="pagination"
    :server-items-length="totalItems"
    class="table-rounded text--uppercase"
    calculate-widths
    fixed-header
    dense
  >
    <template #top>
      <search-panel
        :rightDrawer="rightDrawer"
        @cancelSearch="cancelSearch"
        @resetFilter="resetFilter"
      >
        <v-form ref="formFilter">
          <v-row class="mr-2 offset-1 overline" dense>
            <v-col cols="12" xs="12" class="py-0">
              <v-text-field
                v-model="filters.matricula"
                prepend-icon="mdi-magnify"
                type="text"
                label="Matricula"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" xs="12" class="py-0">
              <v-text-field
                v-model="filters.serie"
                prepend-icon="mdi-magnify"
                type="text"
                label="Serie"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" xs="12" class="py-0">
              <v-text-field
                v-model="filters.ticket_card"
                prepend-icon="mdi-magnify"
                type="text"
                label="TicketCard"
                clearable
              ></v-text-field>
            </v-col>
            <v-col cols="12" xs="12" class="py-0">
              <v-select
                v-model="filters.agencie"
                :items="options.agencies"
                item-text="title"
                item-value="id"
                label="Sucursal"
                prepend-icon="mdi-filter-variant"
                hide-details
                outlined
                filled
                clearable
                dense
                class="mb-2"
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-autocomplete
                v-model="filters.responsable"
                :items="options.employees"
                item-text="full_name"
                item-value="user.id"
                placeholder="Responsable"
                prepend-icon="mdi-filter-variant"
                hide-details
                outlined
                filled
                clearable
                dense
                class="mb-2"
              ></v-autocomplete>
            </v-col>
          </v-row>
        </v-form>
      </search-panel>
      <v-card class="d-flex justify-end align-center flex-wrap px-3 py-1" flat>
        <v-card
          flat
          class="d-flex d-flex justify-space-between align-center flex-wrap py-2"
          :class="'flex-grow-1 flex-shrink-0'"
        >
          <v-text-field
            v-model="search"
            label="Buscar"
            class="pa-2"
            prepend-icon="mdi-magnify"
            hide-details
            clearable
            outlined
            filled
            dense
          ></v-text-field>
        </v-card>
        <v-spacer></v-spacer>
        <v-divider class="mx-2" inset vertical></v-divider>
        <table-header-buttons
          :updateSearchPanel="updateSearchPanel"
          :reloadTable="reloadTable"
        ></table-header-buttons>
      </v-card>
      <v-toolbar flat>
        <v-toolbar-title>Flotilla de Vehiculos</v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          class="mb-2"
          :to="{ name: 'vehicle.create' }"
          rounded
        >
          Registrar Vehiculo
        </v-btn>
      </v-toolbar>
      <dialog-component
        :show="dialogEdit"
        @close="dialogEdit = false"
        fullscreen
        closeable
        :title="`Detalle del Vehiculo: ${editedItem.matricula}`"
      >
        <edit-vehicle :vehicleId="editedId"></edit-vehicle>
      </dialog-component>
    </template>
    <template #[`item.actions`]="{ item }">
      <v-menu offset-x transition="slide-x-transition" rounded="r-xl">
        <template #activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" v-on="on">
            <v-icon>mdi-dots-vertical</v-icon>
          </v-btn>
        </template>
        <v-list shaped dense>
          <v-list-item-group>
            <v-list-item @click="editItem(item)">
              <v-list-item-icon>
                <v-icon class="blue--text">mdi-information-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Info Unidad</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item
              v-if="item.can_dispersal"
              :to="{
                name: 'vehicle.dispersal.create',
                params: {
                  propVehicleId: item.id,
                },
              }"
            >
              <v-list-item-icon>
                <v-icon class="blue--text">mdi-gas-station-outline</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Solicitar Disperciones</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
            <v-list-item
              :to="{
                name: 'vehicle.services.create',
                params: {
                  propVehicleId: item.id,
                },
              }"
            >
              <v-list-item-icon>
                <v-icon class="blue--text">mdi-tools</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title>Solicitar Servicios</v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-menu>
    </template>
    <template #[`item.matricula`]="{ item }">
      <div class="d-flex flex-column">
        <span class="d-block font-weight-semibold text--primary text-truncate">
          {{ item.matricula }}
        </span>
        <small>{{ item.serie }}</small>
      </div>
    </template>
    <template #[`item.model`]="{ item }">
      <div class="d-flex flex-column">
        <span class="d-block font-weight-semibold text--primary text-truncate">
          {{ item.model }}
        </span>
        <small>{{ item.year }}</small>
      </div>
    </template>

    <template #[`item.fuel_odometer`]="{ value }">
      <v-progress-linear v-if="value" :value="value" height="25" dark>
        <strong>{{ value }}%</strong>
      </v-progress-linear>
      <v-icon v-else color="grey">mdi-fuel</v-icon>
    </template>
    <template #[`item.responsable`]="{ item }">
      <v-edit-dialog
        v-if="
          $gate.allow('canAsignar', 'vehicles') && item.responsable === null
        "
        large
        persistent
        save-text="asignar"
        @save="save(item)"
        @cancel="cancel"
        @open="open"
      >
        <v-btn small color="primary" dark>Asignar</v-btn>
        <template #input>
          <v-form v-model="valid" ref="formUserAssigned" lazy-validation>
            <div class="mt-4 title">Responsable para: {{ item.matricula }}</div>
            <v-autocomplete
              v-model="item.user_id"
              :items="options.employees"
              :rules="[(v) => !!v || 'Es Requerido']"
              item-text="name"
              item-value="id"
              placeholder="Buscar por nombre"
              autofocus
            ></v-autocomplete>
          </v-form>
        </template>
      </v-edit-dialog>
      <small
        v-else-if="item.responsable"
        class="d-block font-weight-semibold text-truncate"
      >
        {{ item.responsable.name }}
      </small>
    </template>

    <template #[`item.ticket.account_balance`]="{ value }">
      <span class="d-block font-weight-semibold text-truncate">
        {{ value | currency }}
      </span>
    </template>
  </v-data-table>
</template>

<script>
import SearchPanel from "@admin/components/shared/SearchPanel.vue";
import TableHeaderButtons from "@admin/components/shared/TableHeaderButtons.vue";
import DialogComponent from "@admin/components/DialogComponent.vue";
import EditVehicle from "./Edit.vue";
export default {
  name: "VehicleList",
  components: { TableHeaderButtons, SearchPanel, DialogComponent, EditVehicle },
  mounted() {
    const _this = this;
    _this.reloadTable();
    _this.loadOptions();
    _this.$eventBus.$on(["VEHICLE_REFRESH"], () => {
      _this.reloadTable();
    });
  },
  beforeUpdate() {
    this.$store.commit("setBreadcrumbs", [{ label: "Flotilla", name: "" }]);
  },
  data() {
    return {
      // menu: false,
      valid: true,
      dialogCreate: false,
      dialogEdit: false,
      showSearchPanel: false,
      dialogDelete: false,
      headers: [
        { text: "", value: "actions", sortable: false },
        {
          text: "Matricula",
          align: "left",
          sortable: false,
          value: "matricula",
        },
        {
          text: "Modelo",
          align: "left",
          sortable: false,
          value: "model",
        },
        {
          text: "Tanque %",
          align: "center",
          sortable: false,
          value: "fuel_odometer",
          width: 100,
        },
        {
          text: "Sucursal",
          align: "left",
          sortable: false,
          value: "sucursal.title",
        },
        {
          text: "Responsable",
          align: "center",
          sortable: false,
          value: "responsable",
          divider: true,
        },
        {
          text: "Ticket Card",
          align: "center",
          sortable: false,
          value: "ticket.ticket_card",
        },
        {
          text: "Saldo $",
          align: "center",
          sortable: false,
          value: "ticket.account_balance",
        },
      ],
      editedId: -1,
      editedItem: {},
      items: [],
      search: null,
      filters: {
        matricula: null,
        serie: null,
        model: null,
        year: null,
        brand: null,
        ticket_card: null,
        fuel_id: null,
        responsable: null,
        agencie: null,
      },
      options: {
        users: [],
        employees: [],
        agencies: [],
        // estatus: [
        //   { text: "Pendientes", value: "pendiente" },
        //   { text: "Autorizados", value: "autorizado" },
        //   { text: "Verificados", value: "verificado" },
        //   { text: "Rechazados", value: "denegar" },
        //   { text: "Facturados", value: "programar_pago" },
        //   { text: "Todos", value: "todos" },
        // ],
      },
      totalItems: 0,
      pagination: {
        itemsPerPage: 10,
        page: 1,
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
  },
  watch: {
    pagination: {
      handler: _.debounce(function () {
        this.reloadTable();
      }, 999),
      deep: true,
    },
    filters: {
      handler: _.debounce(function (v) {
        this.reloadTable();
      }, 999),
      deep: true,
    },
    search: _.debounce(function (v) {
      this.reloadTable();
    }, 999),
  },
  methods: {
    updateSearchPanel() {
      this.rightDrawer = !this.rightDrawer;
    },
    reloadTable() {
      this.loadVehicles(() => {});
    },
    cancelSearch() {
      this.showSearchPanel = false;
    },
    editItem(item) {
      this.editedId = item.id;
      this.editedItem = item;
      this.dialogEdit = true;
    },
    deleteItem(item) {
      this.editedId = item.id;
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    closeDelete() {
      this.$nextTick(() => {
        // this.editedItem = Object.assign({}, this.defaultItem);
        this.editedItem = { ...this.defaultItem };
        this.editedId = -1;
      });
      this.dialogDelete = false;
    },

    async loadVehicles(cb) {
      const _this = this;
      let params = {
        ..._this.filters,
        search: _this.search,
        page: _this.pagination.page,
        per_page: _this.pagination.itemsPerPage,
      };
      await axios
        .get("/admin/vehicles", { params: params })
        .then(function (response) {
          let Response = response.data.data;
          _this.items = Response.data;
          _this.totalItems = Response.total;
          _this.pagination.totalItems = Response.total;
          (cb || Function)();
        });
    },

    async loadOptions(cb) {
      const _this = this;
      await axios
        .get(`/admin/vehicles/resources/options`)
        .then(function (response) {
          let Data = response.data.data;
          _this.options.users = Data.users;
          _this.options.employees = Data.employees;
          _this.options.agencies = Data.agencies;
          (cb || Function)();
        });
    },

    resetFilter() {
      const _this = this;
      _this.$refs.formFilter.reset();
      _this.pagination.itemsPerPage = 10;
      _this.pagination.page = 1;
    },

    async save(item) {
      const _this = this;
      if (!this.$refs.formUserAssigned.validate()) return;
      await axios
        .put("/admin/vehicles/" + item.id, item)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: "Usuario Asignado",
            color: "success",
            duration: 3000,
          });
          _this.reloadTable();
        })
        .catch(function (error) {
          if (error.response) {
            this.$store.commit("showSnackbar", {
              message: `Usuario Asignado`,
              color: "success",
              duration: 2000,
            });
          } else if (error.request) {
            console.log(error.request);
          } else {
            console.log("Error", error.message);
          }
        });
    },
    cancel() {
      this.$store.commit("showSnackbar", {
        message: "Cancelado",
        color: "error",
        duration: 2000,
      });
    },
    open() {
      this.$store.commit("showSnackbar", {
        message: "Asignar Responsable a la Unidad",
        color: "primary",
        duration: 3000,
      });
    },
  },
};
</script>

<style scoped></style>
