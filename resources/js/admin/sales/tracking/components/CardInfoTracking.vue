<template>
  <v-card class="mx-auto" elevation="0" flat>
    <v-list>
      <v-list-item>
        <v-list-item-icon v-show="this.$vuetify.breakpoint.mdAndUp">
          <v-icon color="indigo"> mdi-file-document </v-icon>
        </v-list-item-icon>
        <v-list-item-content class="px-2">
          <v-card
            class="mx-auto"
            outlined
            elevation="4"
            color="light-blue lighten-5"
          >
            <v-toolbar flat dense class="overline">
              {{ info.detail.title }}
              <v-spacer></v-spacer>
              {{ info.detail.first_contact }} |
              {{ info.detail.tracking_condition }}
            </v-toolbar>
            <v-divider></v-divider>
            <v-list-item three-line>
              <v-list-item-content>
                <v-list-item-title class="headline text-wrap mb-1">
                  {{ info.detail.reference }}
                </v-list-item-title>
                <v-list-item-subtitle class="subtitle-1 text-uppercase">
                  {{ created_at_format }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="subtitle-1 text-uppercase">
                  {{ info.detail.price | currency }} {{ info.currency }} <br />
                  T.C. {{ info.detail.exchange_value | currency }}
                </v-list-item-subtitle>
                <v-list-item-title class="pt-4 text-wrap text-button" />
                {{ info.detail.description_topic }}
              </v-list-item-content>
            </v-list-item>
          </v-card>
        </v-list-item-content>
      </v-list-item>

      <v-list-item>
        <v-list-item-icon v-show="this.$vuetify.breakpoint.mdAndUp">
          <v-icon color="indigo"> mdi-account </v-icon>
        </v-list-item-icon>
        <v-list-item-content class="px-2">
          <v-card
            class="mx-auto"
            outlined
            elevation="4"
            color="light-blue lighten-5"
          >
            <v-toolbar flat dense class="overline">
              Prospecto
              <v-spacer />
              <v-dialog v-model="customer_dialog" persistent max-width="600px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn color="primary" dark v-bind="attrs" v-on="on">
                    Asociar Cliente
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="text-h5">Asociar con un Cliente</span>
                  </v-card-title>
                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12">
                          <v-autocomplete
                            v-model="customer_id"
                            :items="customers"
                            item-value="id"
                            item-text="full_name"
                            label="Seleccionar Cliente"
                            :filter="customFilter"
                            outlined
                            required
                          >
                            <template v-slot:prepend-item>
                              <v-list dense color="grey lighten-3">
                                <v-list-item @click="dialogCreate = true">
                                  <v-list-item-content>
                                    <v-list-item-title />
                                    Registar Nuevo Cliente
                                  </v-list-item-content>
                                  <v-list-item-action>
                                    <v-icon>mdi-account-plus</v-icon>
                                  </v-list-item-action>
                                </v-list-item>
                              </v-list>
                            </template>
                            <template v-slot:item="{ item }">
                              <v-list-item-title>
                                {{ item.full_name }}
                              </v-list-item-title>
                              <v-list-item-subtitle>
                                {{ item.rfc }}
                              </v-list-item-subtitle>
                            </template>
                          </v-autocomplete>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      color="blue darken-1"
                      text
                      @click="customer_dialog = false"
                    >
                      Cancelar
                    </v-btn>
                    <v-btn
                      color="blue darken-1"
                      text
                      @click="associateCustomer()"
                    >
                      Guardar
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-toolbar>
            <v-divider></v-divider>
            <v-list-item three-line>
              <v-list-item-content>
                <v-list-item-title class="headline text-wrap mb-2">
                  {{ info.prospect.full_name }}
                </v-list-item-title>
                <v-list-item-subtitle class="body-1">
                  {{ info.prospect.company || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">RFC:</span>
                  {{ info.prospect.rfc || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Telefono:</span>
                  {{ info.prospect.phone || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Email:</span>
                  {{ info.prospect.email || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle
                  v-if="info.prospect.township"
                  class="body-1"
                >
                  <span class="text--primary">Ciudad:</span>
                  {{ info.prospect.township.name }},
                  {{ info.prospect.township.estate.name }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Rancho o Provincia:</span>
                  {{ info.prospect.town || "" }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-card>
        </v-list-item-content>
      </v-list-item>
      <v-list-item v-if="info.customer">
        <v-list-item-icon v-show="this.$vuetify.breakpoint.mdAndUp">
          <v-icon color="indigo"> mdi-account </v-icon>
        </v-list-item-icon>
        <v-list-item-content class="px-2">
          <v-card
            class="mx-auto"
            outlined
            elevation="4"
            color="light-blue lighten-5"
          >
            <v-toolbar flat dense class="overline"> Cliente </v-toolbar>
            <v-divider></v-divider>
            <v-list-item three-line>
              <v-list-item-content>
                <v-list-item-title class="headline text-wrap mb-2">
                  {{ info.customer.full_name }}
                </v-list-item-title>
                <v-list-item-subtitle class="body-1">
                  {{ info.customer.company || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">RFC:</span>
                  {{ info.customer.rfc || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Telefono:</span>
                  {{ info.customer.phone || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Email:</span>
                  {{ info.customer.email || "" }}
                </v-list-item-subtitle>
                <v-list-item-subtitle
                  v-if="info.prospect.township"
                  class="body-1"
                >
                  <span class="text--primary">Ciudad:</span>
                  {{ info.customer.township.name }},
                  {{ info.customer.township.estate.name }}
                </v-list-item-subtitle>
                <v-list-item-subtitle class="body-1">
                  <span class="text--primary">Rancho o Provincia:</span>
                  {{ info.customer.town || "" }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-card>
        </v-list-item-content>
      </v-list-item>

      <v-list-item>
        <v-list-item-icon v-show="this.$vuetify.breakpoint.mdAndUp">
          <v-icon color="indigo"> mdi-account-box-outline </v-icon>
        </v-list-item-icon>

        <v-list-item-content class="px-2">
          <v-card
            class="mx-auto"
            outlined
            elevation="4"
            color="light-blue lighten-5"
          >
            <v-toolbar flat dense class="overline">
              Promotor
              <v-spacer></v-spacer>
            </v-toolbar>
            <v-divider></v-divider>
            <v-list-item three-line>
              <v-list-item-content>
                <v-list-item-subtitle>
                  Ejecutivo asignado:
                </v-list-item-subtitle>
                <v-list-item-title class="headline mb-1">
                  {{ info.attended_by }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ info.attended_email }}
                </v-list-item-subtitle>
                <v-list-item-subtitle>
                  {{ info.agency }} - {{ info.department }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-card>
        </v-list-item-content>
      </v-list-item>
    </v-list>

    <dialog-component
      :show="dialogCreate"
      @close="dialogCreate = false"
      closeable
      :fullscreen="$vuetify.breakpoint.mobile"
      :title="`Registrar Cliente`"
      max-width="900"
    >
      <create-customer
        @cancel="(dialogCreate = false), loadCustomers()"
      ></create-customer>
    </dialog-component>
  </v-card>
</template>

<script>
import Assertiveness from "@admin/sales/tracking/resources/assertiveness.json";
import CreateCustomer from "../../../customers/customers/Create.vue";
import DialogComponent from "@admin/components/DialogComponent.vue";
export default {
  components: { CreateCustomer, DialogComponent },
  props: {
    info: {
      required: true,
      type: Object,
    },
  },
  mounted() {
    this.loadCustomers();
  },
  data() {
    return {
      customer_dialog: false,
      customers: [],
      customer_id: null,
      dialogCreate: false,
    };
  },
  computed: {
    folio() {
      return this.info.detail.id
        ? this.info.detail.id.toString().padStart(5, 0)
        : "";
    },
    percenAssertiveness() {
      return Assertiveness.find((item) => {
        return item.value == this.info.detail.assertiveness;
      });
    },
    created_at_format() {
      return this.$appFormatters.formatDate(
        this.info.detail.created_at,
        "LL hh:mm a"
      );
    },
  },
  methods: {
    colorEstatus(value) {
      if (value == "finalizado") return "red";
      else if (value == "formalizado") return "blue";
      else return "primary";
    },
    async loadCustomers() {
      const _this = this;
      let params = {
        paginate: "no",
      };
      await axios
        .get("/admin/customers", { params: params })
        .then(function (response) {
          let { data } = response.data;
          _this.customers = data;
        });
    },
    associateCustomer() {
      const _this = this;
      let params = {
        customer_id: _this.customer_id,
      };
      axios
        .put(
          `/admin/tracking/associateCustomer/${_this.info.detail.id}`,
          params
        )
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          _this.customer_dialog = false;
          _this.$eventBus.$emit("MESSAGE_ADDED");
          // self.loadTrackings(() => {});
          // cb();
        });
    },
    customFilter(item, queryText, itemText) {
      const words = queryText.toLowerCase().split(" ");

      return words.every((word) => {
        const nameMatch = item.full_name.toLowerCase().includes(word);
        const rfcMatch = item.rfc
          ? item.rfc.toLowerCase().includes(word)
          : false;
        // const categoryNameMatch = item.category.name
        //   .toLowerCase()
        //   .includes(word);

        return nameMatch || rfcMatch;
      });
    },
  },
};
</script>

<style scoped>
.v-progress-circular {
  margin: 1rem;
}
.v-toolbar__content {
  padding: 0;
}
</style>
