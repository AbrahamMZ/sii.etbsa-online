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
            rounded="xl"
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

          <!-- <v-card>
            <v-card-title>
              {{ info.detail.title }}
              <v-spacer />
              <v-icon>mdi-information</v-icon>
            </v-card-title>
            <v-card-subtitle>
              {{ info.detail.description_topic }}
            </v-card-subtitle>

            <VCardText class="pt-3">
              <VRow class="gap-y-1">
                <VCol cols="12" sm="6">
                  <p class="mb-1">Valor Estimado:</p>
                  <h6>
                    {{ info.detail.price | currency }} {{ info.currency }}
                  </h6>
                </VCol>

                <VCol cols="12" sm="6">
                  <p class="mb-1">Tipo de Cambio</p>
                  <h6>
                    {{ info.detail.exchange_value | currency }}
                  </h6>
                </VCol>

                <VCol cols="12" sm="6">
                  <p class="mb-1">Fecha de Creacion:</p>
                  <h6>
                    {{ created_at_format }}
                  </h6>
                </VCol>

                <VCol>
                  <p>Porcentaje Certeza:</p>
                  <div class="d-flex align-center" style="width: 130px">
                    <v-tooltip bottom>
                      <template v-slot:activator="{ on, attrs }">
                        <VProgressLinear
                          v-bind="attrs"
                          v-on="on"
                          :color="percenAssertiveness.color"
                          :value="info.detail.assertiveness * 100"
                          :height="6"
                          class="me-4"
                        >
                        </VProgressLinear>
                        <span class="text--primary">
                          {{ info.detail.assertiveness | percent }}
                        </span>
                      </template>
                      <span>{{ percenAssertiveness.text }}</span>
                    </v-tooltip>
                  </div>
                </VCol>

                <VCol cols="12" sm="6">
                  <p class="mb-1">Vendedor</p>
                  <div class="text-h6">{{ info.attended_by }}</div>
                </VCol>

                <VCol>
                  <VChip color="primary" size="small" class="mt-2">
                    {{ info.detail.date_next_tracking }}
                  </VChip>
                </VCol>
              </VRow>
            </VCardText>
          </v-card> -->
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

              <v-btn color="primary" dark @click="dialogEdit = true">
                Editar Prospecto
              </v-btn>

              <dialog-component
                :show="dialogEdit"
                @close="dialogEdit = false"
                closeable
                :fullscreen="$vuetify.breakpoint.mobile"
                title="Editar Prospecto"
                :maxWidth="600"
              >
                <prospect-edit
                  :propProspectId="info.prospect.id"
                  :key="info.prospect.full_name"
                ></prospect-edit>
              </dialog-component>
            </v-toolbar>
            <v-divider></v-divider>
            <v-list-item three-line>
              <v-list-item-content>
                <v-list-item-title class="headline text-wrap mb-2">
                  {{ info.prospect.full_name }}
                </v-list-item-title>
                <v-list-item-subtitle class="body-1">
                  {{
                    info.prospect?.customer?.full_name ||
                    "Prospecto Sin Cliente Asociado"
                  }}
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
      <!-- <v-list-item v-if="info.customer">
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
      </v-list-item> -->

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
  </v-card>
</template>

<script>
import DialogComponent from "@admin/components/DialogComponent.vue";
import Assertiveness from "@admin/sales/tracking/resources/assertiveness.json";
import ProspectEdit from "@admin/sales/prospect/ProspectEdit.vue";
export default {
  components: { DialogComponent, ProspectEdit },
  props: {
    info: {
      required: true,
      type: Object,
    },
  },
  mounted() {
    // this.loadCustomers();
  },
  data() {
    return {
      dialogEdit: false,
      customer_dialog: false,
      customers: [],
      customer_id: null,
    };
  },
  computed: {
    folio() {
      return this.info.detail.id
        ? this.info.detail.id.toString().padStart(5, 0)
        : "";
    },
    percenAssertiveness() {
      const _this = this;
      return Assertiveness.find((item) => {
        return item.value == _this.info?.detail?.assertiveness;
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
    // async loadCustomers() {
    //   const _this = this;
    //   let params = {
    //     paginate: "no",
    //   };
    //   await axios
    //     .get("/admin/customers", { params: params })
    //     .then(function (response) {
    //       let { data } = response.data;
    //       _this.customers = data;
    //     });
    // },
    // associateCustomer() {
    //   const _this = this;
    //   let params = {
    //     customer_id: _this.customer_id,
    //   };
    //   axios
    //     .put(
    //       `/admin/tracking/associateCustomer/${_this.info.detail.id}`,
    //       params
    //     )
    //     .then(function (response) {
    //       _this.$store.commit("showSnackbar", {
    //         message: response.data.message,
    //         color: "success",
    //         duration: 3000,
    //       });
    //       _this.customer_dialog = false;
    //       _this.$eventBus.$emit("MESSAGE_ADDED");
    //       // self.loadTrackings(() => {});
    //       // cb();
    //     });
    // },
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
