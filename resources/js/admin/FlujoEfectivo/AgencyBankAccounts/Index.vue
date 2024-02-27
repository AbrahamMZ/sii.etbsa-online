<template>
  <div>
    <v-data-table :headers="headers" :items="items" class="elevation-1" dense>
      <template v-slot:top>
        <v-toolbar flat class="align-center">
          <v-toolbar-title>Cuentas de Sucursales</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>

        <v-text-field label="Buscar" class="mr-1" hide-details outlined dense></v-text-field>

          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark v-bind="attrs" v-on="on">
                Registrar Nueva Cuenta
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
                      <v-text-field
                        v-model="editedItem.bank_name"
                        label="Nombre del Banco"
                        outlined
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-select
                        v-model="editedItem.agency_id"
                        :items="options.agencies"
                        item-value="id"
                        item-text="title"
                        label="Sucursal"
                        outlined
                      ></v-select>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.account_number"
                        label="Numeor de Cuenta"
                        outlined
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.balance"
                        label="Balance"
                        outlined
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">
                  Cancel
                </v-btn>
                <v-btn color="blue darken-1" text @click="save"> Save </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="text-h5">
                Seguro en Elimar Cuenta de Sucursal?
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

        <v-btn color="accent" class="ml-2" @click="initialize" dark> Actualizar </v-btn>
      </v-toolbar>
    </template>
    <template v-slot:[`item.actions`]="{ item }">
      <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
      <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
    </template>
    <template v-slot:no-data>
      <v-btn color="primary" class="ml-1" @click="initialize"> Reset </v-btn>
    </template>
  </v-data-table>
</template>

<script>
export default {
  data: () => ({
    dialog: false,
    dialogDelete: false,
    tablaPolizas: {},
    data: [],
    headers: [
      { text: "ID", value: "id" },
      { text: "Nombre Banco", value: "bank_name" },
      {
        text: "Sucursal",
        align: "start",
        sortable: false,
        value: "agency.title",
      },
      { text: "Numero Cuenta", value: "account_number" },
      { text: "Balance Ingreso", value: "income_balance" },
      { text: "Balance", value: "balance" },
      { text: "Saldo Polizas", value: "calculo" },
      { text: "Actions", value: "actions", sortable: false },
    ],
    items: [],
    options: {
      agencies: [],
    },
    editedIndex: -1,
    editedItem: {
      id: null,
      agency_id: null,
      bank_name: null,
      account_number: null,
      balance: null,
    },
    defaultItem: {
      id: null,
      agency_id: null,
      bank_name: null,
      account_number: null,
      balance: null,
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Nueva Cuenta" : "Editar Cuenta";
    },
    // cuentas() {
    //   // Obtener un conjunto único de cuentas
    //   return [...new Set(this.data.map((item) => item.id))];
    // },
    // polizasPorTipo() {
    //   // Agrupar las pólizas por tipo de póliza
    //   const polizasPorTipo = {};
    //   this.data.forEach((item) => {
    //     if (!polizasPorTipo[item.tipo_poliza_id]) {
    //       polizasPorTipo[item.tipo_poliza_id] = {};
    //     }
    //     polizasPorTipo[item.tipo_poliza_id][item.id] = item.poliza_id;
    //   });
    //   return polizasPorTipo;
    // },
    // tiposPoliza() {
    //   // Obtener un conjunto único de tipos de póliza
    //   const tiposPoliza = {};
    //   this.table.forEach((cuenta) => {
    //     for (const tipoPolizaId in cuenta[Object.keys(cuenta)[0]]) {
    //       if (!tiposPoliza[tipoPolizaId]) {
    //         tiposPoliza[tipoPolizaId] = true;
    //       }
    //     }
    //   });
    //   return tiposPoliza;
    // },
    // table() {
    //   const result = [];

    //   // Procesamos los datos originales
    //   this.data.forEach((item) => {
    //     const cuentaIndex = result.findIndex((cuenta) => cuenta[item.id]);
    //     if (cuentaIndex === -1) {
    //       // Si la cuenta no existe, la agregamos al resultado
    //       result.push({
    //         [item.id]: { [item.tipo_poliza_id]: [item.poliza_id] },
    //       });
    //     } else {
    //       const cuenta = result[cuentaIndex];
    //       if (cuenta[item.id][item.tipo_poliza_id]) {
    //         // Si el tipo de póliza existe, agregamos la póliza
    //         cuenta[item.id][item.tipo_poliza_id].push(item.poliza_id);
    //       } else {
    //         // Si el tipo de póliza no existe, lo creamos y agregamos la póliza
    //         cuenta[item.id][item.tipo_poliza_id] = [item.poliza_id];
    //       }
    //     }
    //   });
    //   return result;
    // },
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
    this.initialize();
  },

  methods: {
    async initialize() {
      const _this = this;
      try {
        let {
          data: { data, message },
        } = await axios.get("/admin/agency-bank-accounts");
        _this.items = data.accounts;
        _this.data = data.polizasPorTipo;
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
      // try {
      await axios.delete(`/admin/agency-bank-accounts/${_this.editedIndex}`);
      // } catch (error) {}
      this.closeDelete();
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
            `/admin/agency-bank-accounts/${_this.editedIndex}`,
            _this.editedItem
          );
        } catch (error) {}
      } else {
        try {
          await axios.post(`/admin/agency-bank-accounts`, _this.editedItem);
        } catch (error) {}
      }
      this.close();
      this.initialize();
    },
  },
};
</script>