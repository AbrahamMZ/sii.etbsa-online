<template>
  <v-data-table
    :headers="headers"
    :items="items"
    :options.sync="pagination"
    :server-items-length="totalItems"
    class="elevation-3 text-uppercase font-weight-bold"
    show-expand
    single-expand
    :expanded.sync="expanded"
    dense
  >
    <template v-slot:top>
      <v-toolbar flat class="align-center">
        <v-toolbar-title>{{ propTipoPoliza }}</v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>

        <v-text-field
          label="Buscar"
          class="mr-1"
          hide-details
          outlined
          dense
        ></v-text-field>

        <v-dialog v-model="dialog" max-width="600px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark v-bind="attrs" v-on="on">
              Registrar {{ propTipoPoliza }}
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>

            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-select
                      v-model.number="editedItem.origen_bank_accounts_id"
                      :items="options.agency_bank_accounts"
                      item-value="id"
                      item-text="bank_name"
                      label="Cuenta de Origen"
                      outlined
                      dense
                    ></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-select
                      v-model.number="editedItem.apply_bank_accounts_id"
                      :items="options.agency_bank_accounts"
                      item-value="id"
                      item-text="bank_name"
                      label="Cuenta de Destino"
                      outlined
                      dense
                    ></v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model.number="editedItem.amount"
                      label="Importe"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model.number="editedItem.currency_id"
                      :items="options.currencies"
                      item-value="id"
                      item-text="name"
                      label="Moneda"
                      outlined
                      dense
                    ></v-select>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-select
                      v-model.number="editedItem.payment_source_id"
                      :items="options.payment_sources"
                      item-value="id"
                      item-text="title"
                      label="Forma Pago"
                      outlined
                      dense
                    ></v-select>
                  </v-col>
                  <!-- <v-col cols="12" md="6"> 
                    <v-select
                      v-model.number="editedItem.category_id"
                      :items="options.category"
                      item-value="id"
                      item-text="name"
                      label="Categoria"
                      outlined
                      dense
                    ></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="editedItem.reference_number"
                      label="Numero Cliente"
                      persistent-hint
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="editedItem.reference_name"
                      label="Nombre Cliente"
                      persistent-hint
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="editedItem.reference_concept"
                      label="Referencia"
                      hint="Folio, Factura, Numero de Referencia"
                      persistent-hint
                      outlined
                      dense
                    ></v-text-field>
                  </v-col> -->
                  <v-col cols="12">
                    <v-textarea
                      v-model="editedItem.description"
                      label="Descripcion"
                      outlined
                      dense
                    ></v-textarea>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="close"> Cancel </v-btn>
              <v-btn color="blue darken-1" text @click="save"> Guardar </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">
              Seguro en Eliminar Poliza?
            </v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">
                Cancel
              </v-btn>
              <v-btn color="blue darken-1" text @click="deleteItemConfirm">
                OK
              </v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogApply" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">
              Seguro en aplicar la Poliza?
            </v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeApply">
                Cancel
              </v-btn>
              <v-btn color="blue darken-1" text @click="applyItemConfirm">
                OK
              </v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogUnapply" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">
              Seguro en Desaplicar la Poliza?
            </v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeUnapply">
                Cancel
              </v-btn>
              <v-btn color="blue darken-1" text @click="unapplyItemConfirm">
                OK
              </v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <v-btn color="accent" class="ml-2" @click="initialize" dark>
          Actualizar
        </v-btn>
      </v-toolbar>
    </template>
    <template v-slot:[`item.amount`]="{ item, value }">
      {{ value | money }} - {{ item?.currency?.name }}
    </template>
    <template v-slot:[`item.is_applied`]="{ item, value }">
      <v-icon
        :color="value ? 'primary' : 'grey'"
        @click="value ? unapplyItem(item) : applyItem(item)"
      >
        mdi-check-circle
      </v-icon>
    </template>

    <template v-slot:[`item.created_at`]="{ value }">
      {{ $appFormatters.formatDate(value) }}
    </template>
    <template v-slot:[`item.actions`]="{ item }">
      <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
      <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
    </template>
    <!-- <template v-slot:no-data>
      <v-btn color="primary" class="ml-1" @click="initialize"> Reset </v-btn>
    </template> -->

    <template v-slot:expanded-item="{ headers, item }">
      <td :colspan="headers.length" class="pa-0">
        <v-simple-table dense fixed-header class="text-subtitle-2" dark>
          <template v-slot:default>
            <tbody>
              <tr>
                <td>Descripcion:</td>
                <td>{{ item.description }}</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </td>
    
    </template>
  </v-data-table>
</template>

<script>
export default {
  props: ["propTipoPoliza"],
  data: () => ({
    dialog: false,
    expanded: [],
    dialogDelete: false,
    dialogApply: false,
    dialogUnapply: false,
    headers: [
      {
        text: "Cuenta Origen",
        align: "start",
        sortable: false,
        value: "origen_agency_bank_account.bank_name",
      },
      {
        text: "Cuenta Destino",
        align: "start",
        sortable: false,
        value: "apply_agency_bank_account.bank_name",
      },
      { text: "Forma Pago", value: "payment_source.title" },
      { text: "Importe", value: "amount" },
      // { text: "Categoria", value: "category.name" },
      { text: "Fecha Registro", value: "created_at" },
      { text: "Usuario Registro", value: "created_user.name" },
      { text: "Aplicado", value: "is_applied" },

      { text: "Actions", value: "actions", sortable: false },
    ],
    items: [],
    options: {
      agency_bank_accounts: [],
      currencies: [],
      payment_sources: [],
      category: [],
    },
    pagination: {
      itemsPerPage: 10,
      page: 1,
    },
    totalItems: 0,
    editedIndex: -1,
    editedItem: {
      // external_id: null,
      reference_number: null,
      reference_name: null,
      reference_concept: null,
      description: null,
      amount: 0,
      currency_id: 1,
      payment_source_id: 1,
      tipo_poliza_id: 3,
      category_id: null,
      origen_bank_accounts_id: null,
      apply_bank_accounts_id: null,
      is_applied: false,
      // apply_date: null,
      // user_created: null,
      // user_updated: null,
    },
    defaultItem: {
      // external_id: null,
      reference_number: null,
      reference_name: null,
      reference_concept: null,
      description: null,
      amount: 0,
      currency_id: 1,
      payment_source_id: 1,
      tipo_poliza_id: 3,
      category_id: null,
      origen_bank_accounts_id: null,
      apply_bank_accounts_id: null,
      is_applied: false,
      // apply_date: null,
      // user_created: null,
      // user_updated: null,
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar Poliza" : "Editar Poliza";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
    pagination: {
      handler: _.debounce(function (v) {
        this.initialize(() => {});
      }, 700),
      deep: true,
    },
  },

  mounted() {
    this.initialize();
    this.getOptions();
  },

  methods: {
    async initialize() {
      const _this = this;

      let params = {
        tipo_poliza: "transferencia",
        page: _this.pagination.page,
        per_page: _this.pagination.itemsPerPage,
      };

      try {
        let {
          data: { data, message },
        } = await axios.get("/admin/polizas", { params });
        _this.items = data.data;
        _this.totalItems = data.total;
        _this.pagination.totalItems = data.total;

        _this.$store.commit("showSnackbar", {
          message: message,
          color: "success",
          duration: 3000,
        });
      } catch (error) {
        _this.$store.commit("showSnackbar", {
          message: error.response.data.message,
          color: "error",
          duration: 3000,
        });
      }
    },

    editItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    async deleteItemConfirm() {
      const _this = this;
      try {
        console.log("DELETE", _this.editedIndex);
        await axios.delete(`/admin/polizas/${_this.editedIndex}`);
      } catch (error) {}
      this.closeDelete();
      this.initialize();
    },

    async applyItemConfirm() {
      const _this = this;
      try {
        await axios.put(`/admin/polizas/${_this.editedIndex}/apply`);
      } catch (error) {}
      this.closeApply();
      this.initialize();
    },
    async unapplyItemConfirm() {
      const _this = this;
      try {
        await axios.put(`/admin/polizas/${_this.editedIndex}/unapply`);
      } catch (error) {}
      this.closeUnapply();
      this.initialize();
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

    async save() {
      const _this = this;
      if (this.editedIndex > -1) {
        try {
          await axios.put(
            `/admin/polizas/${_this.editedIndex}`,
            _this.editedItem
          );
        } catch (error) {
          return;
        }
      } else {
        try {
          await axios.post(`/admin/polizas`, _this.editedItem);
        } catch (error) {
          return;
        }
      }
      this.close();
      this.initialize();
    },

    closeApply() {
      this.dialogApply = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },
    closeUnapply() {
      this.dialogUnapply = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    applyItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item);
      this.dialogApply = true;
    },
    unapplyItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item);
      this.dialogUnapply = true;
    },
    async getOptions() {
      const _this = this;
      try {
        let {
          data: { data, message },
        } = await axios.get("/admin/polizas/create");
        _this.options = data.options;

        _this.$store.commit("showSnackbar", {
          message: message,
          color: "success",
          duration: 3000,
        });
      } catch (error) {
        _this.$store.commit("showSnackbar", {
          message: error.response.data.message,
          color: "error",
          duration: 3000,
        });
      }
    },
  },
};
</script>