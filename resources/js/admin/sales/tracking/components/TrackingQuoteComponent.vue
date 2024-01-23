<template>
  <v-data-table :headers="headers" :items="items" hide-default-footer dense>
    <template v-slot:top>
      <v-toolbar flat>
        <v-toolbar-title>
          Cotizaciones del Seguimiento #{{ TrackingId }}</v-toolbar-title
        >
        <v-spacer></v-spacer>
        <v-dialog v-model="dialog" class="overflow-auto" persistent fullscreen>
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
              Crear Nueva Cotizacion
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>

            <v-card-text class="grey-lighten-2">
              <quote-form
                v-if="dialog"
                ref="form"
                :form.sync="editedItem"
              ></quote-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="red darken-1" dark @click="close"> Cancelar </v-btn>
              <v-btn color="blue darken-1" text @click="save">
                {{ formTitle }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-btn text small dark color="primary" class="p-3" @click="initialize">
          Refresh
        </v-btn>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">
              La Cotizacion Se marcara como Perdida.
            </v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" dark @click="closeDelete">
                Cancel
              </v-btn>
              <v-btn color="blue darken-1" text @click="deleteItemConfirm">
                OK
              </v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogWonQuote" max-width="600px">
          <v-card>
            <v-card-title class="text-h5"> Crear Pedido </v-card-title>
            <v-card-text>
              La Cotizacion de Marcara como Ganada, y Se creara un Pedido.
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" dark @click="closeWon">
                Cancel
              </v-btn>
              <v-btn color="blue darken-1" text @click="wonItemConfirm">
                OK
              </v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
    <template #[`item.total`]="{ item, value }">
      {{ value | money }} {{ item.currency.name }}
    </template>
    <template #[`item.products`]="{ value }">
      {{ value.length }}
    </template>
    <template #[`item.updated_at`]="{ value }">
      {{ $appFormatters.formatDate(value, "L") }}
    </template>
    <template #[`item.date_due`]="{ value }">
      {{ $appFormatters.formatDate(value, "L") }}
    </template>
    <template #[`item.actions`]="{ item }">
      <v-btn
        icon
        :href="`/admin/quote/${item.id}/print`"
        target="_blank"
        class="mr-2"
        color="blue"
      >
        <v-icon small> mdi-printer </v-icon>
      </v-btn>
      <template v-if="item.estatus.key == 'activo'">
        <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
        <v-icon small color="red" @click="deleteItem(item)">
          mdi-delete
        </v-icon>
      </template>
      <v-btn icon color="green" class="ml-2" right @click="wonQuote(item)" dark>
        <v-icon small> mdi-file-document </v-icon>
      </v-btn>
    </template>
  </v-data-table>
</template>
<script>
import QuoteForm from "../forms/TrackingQuoteForm.vue";
export default {
  components: { QuoteForm },
  props: {
    TrackingId: {
      type: [Number, String],
      required: true,
    },
  },
  data: () => ({
    renderComponent: true,
    dialog: false,
    dialogDelete: false,
    dialogWonQuote: false,
    headers: [
      {
        text: "Folio",
        align: "start",
        sortable: false,
        value: "id",
      },
      {
        text: "Estatus",
        align: "start",
        sortable: false,
        value: "estatus.title",
      },
      { text: "Condicion de Pago", value: "label_payment" },
      { text: "Total", value: "total" },
      { text: "Num. Partidas", value: "products", align: "center" },
      { text: "F. Creacion", value: "updated_at", align: "end" },
      { text: "F. Vencimiento", value: "date_due", align: "end" },
      { text: "Actions", value: "actions", align: "end", sortable: false },
    ],
    items: [],
    editedIndex: -1,
    editedItem: {
      subtotal: 0,
      discount: 0,
      tax: 0.16,
      total: 0,
      currency: { id: 1, name: "MXN" },
      products: [],
      exchange_value: 1,
      payment_condition: "por_definir",
      category_id: null,
    },
    editedItemDefault: {
      subtotal: 0,
      discount: 0,
      tax: 0.16,
      total: 0,
      currency: { id: 1, name: "MXN" },
      products: [],
      exchange_value: 1,
      payment_condition: "por_definir",
      category_id: null,
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "Crear Cotizacion"
        : "Modificar Cotizacion";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
    dialogWonQuote(val) {
      val || this.closeWon();
    },
  },

  mounted() {
    this.$eventBus.$on(["CREATE_QUOTE", "EDIT_QUOTE", "DELETE_QUOTE"], () => {
      this.initialize();
    });
    this.initialize();
  },

  methods: {
    async initialize() {
      const _this = this;
      let params = {
        trackingId: _this.TrackingId,
        paginate: "no",
      };

      _this.items = await axios.get("/admin/quotes", { params }).then((res) => {
        return res.data.data;
      });
      await axios
        .get("/admin/tracking/sales_history/resources")
        .then(function (response) {
          let { exchange_value } = response.data.data;
          _this.editedItem.exchange_value = exchange_value;
          _this.editedItemDefault.exchange_value = exchange_value;
        });
    },
    async editItem(item) {
      const _this = this;
      _this.editedIndex = item.id;
      item.products = await item.products.map((product) => {
        return {
          ...product,
          prices: Object.keys(product).reduce((acc, key) => {
            if (key.startsWith("price_")) {
              acc[key] = product[key];
            }
            return acc;
          }, {}),
          price: product.quotation.price_unit,
          qty: product.quotation.quantity,
          subtotal: product.quotation.subtotal,
          // currency: item.currency,
          currency: product.currency,
          category_id: product.category.id,
        };
      });
      const [primerProduct = null] = item.products;
      // item.category_id = primerProduct ? primerProduct.category.id : null;
      item.read_only = true;
      _this.editedItem = Object.assign({}, item);
      _this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = item.id;
      // this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },
    wonQuote(item) {
      this.editedIndex = item.id;
      // this.editedItem = Object.assign({}, item);
      this.dialogWonQuote = true;
    },

    async deleteItemConfirm() {
      const _this = this;
      await axios
        .delete(`/admin/quotes/${_this.editedIndex}`)
        .then((response) => {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "error",
            duration: 3000,
          });
          _this.$eventBus.$emit("DELETE_QUOTE");
        })
        .catch(function (error) {
          _this.$store.commit("hideLoader");

          if (error.response) {
            _this.$store.commit("showSnackbar", {
              message: error.response.data.message,
              color: "error",
              duration: 3000,
            });
          } else if (error.request) {
            console.log(error.request);
          } else {
            console.log("Error", error.message);
          }
        });

      _this.closeDelete();
    },
    async wonItemConfirm() {
      const _this = this;

      console.log(_this.editedIndex);
      // await axios
      //   .delete(`/admin/quotes/${_this.editedIndex}`)
      //   .then((response) => {
      //     _this.$store.commit("showSnackbar", {
      //       message: response.data.message,
      //       color: "error",
      //       duration: 3000,
      //     });
      //     _this.$eventBus.$emit("DELETE_QUOTE");
      //   })
      //   .catch(function (error) {
      //     _this.$store.commit("hideLoader");

      //     if (error.response) {
      //       _this.$store.commit("showSnackbar", {
      //         message: error.response.data.message,
      //         color: "error",
      //         duration: 3000,
      //       });
      //     } else if (error.request) {
      //       console.log(error.request);
      //     } else {
      //       console.log("Error", error.message);
      //     }
      //   });

      _this.closeWon();
    },

    close() {
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.editedItemDefault);
        this.editedIndex = -1;
      });
      this.dialog = false;
    },

    closeDelete() {
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.editedItemDefault);
        this.editedIndex = -1;
      });
      this.dialogDelete = false;
    },
    closeWon() {
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.editedItemDefault);
        this.editedIndex = -1;
      });
      this.dialogWonQuote = false;
    },

    async save() {
      const _this = this;
      if (this.editedItem.products.length == 0)
        return this.$store.commit("showSnackbar", {
          message: "Aun no Agrega una Partida",
          color: "warning",
          duration: 3000,
        });
      let payload = {
        ..._this.editedItem,
        // currency: _this.editedItem.currency,
        tracking_id: _this.TrackingId,
      };

      if (_this.editedIndex > -1) {
        await axios
          .put(`/admin/quotes/${_this.editedIndex}`, payload)
          .then((response) => {
            _this.$store.commit("showSnackbar", {
              message: response.data.message,
              color: "success",
              duration: 3000,
            });
            if (!!response.data.data) {
              let idQuote = response.data.data.id;
              window.open(
                `/admin/quote/${idQuote}/print`,
                "_blank",
                "noreferrer"
              );
            }
            _this.$eventBus.$emit("EDIT_QUOTE");
          })
          .catch(function (error) {
            _this.$store.commit("hideLoader");

            if (error.response) {
              _this.$store.commit("showSnackbar", {
                message: error.response.data.message,
                color: "error",
                duration: 3000,
              });
              _this.$emit("EDIT_QUOTE");
            } else if (error.request) {
              console.log(error.request);
            } else {
              console.log("Error", error.message);
            }
          });
      } else {
        await axios
          .post(`/admin/quotes`, payload)
          .then((response) => {
            _this.$store.commit("showSnackbar", {
              message: response.data.message,
              color: "success",
              duration: 3000,
            });
            if (!!response.data.data) {
              let idQuote = response.data.data.id;
              window.open(
                `/admin/quote/${idQuote}/print`,
                "_blank",
                "noreferrer"
              );
            }
            _this.$eventBus.$emit("CREATE_QUOTE");
          })
          .catch(function (error) {
            _this.$store.commit("hideLoader");

            if (error.response) {
              _this.$store.commit("showSnackbar", {
                message: error.response.data.message,
                color: "error",
                duration: 3000,
              });
            } else if (error.request) {
              console.log(error.request);
            } else {
              console.log("Error", error.message);
            }
          });
      }

      this.close();
    },
  },
};
</script>
<style></style>
