<template>
  <v-stepper v-model="step">
    <v-toolbar flat dense>
      <v-toolbar-title class="text-wrap">
        Solicitar Dispercion <b>{{ VehicleName }}</b>
      </v-toolbar-title>
    </v-toolbar>
    <v-stepper-header>
      <v-stepper-step :complete="step > 1" step="1" editable>
        Datos del Vehiculo
      </v-stepper-step>

      <v-divider></v-divider>

      <v-stepper-step :complete="step > 2" step="2">
        Motivo y Cargo
      </v-stepper-step>

      <v-divider></v-divider>

      <v-stepper-step step="3">
        Litros a Solicitar
      </v-stepper-step>
    </v-stepper-header>

    <v-stepper-items>
      <v-stepper-content step="1" class="pa-0">
        <v-card color="grey lighten-3">
          <v-card-text>
            <v-form
              v-model="validFormVehicle"
              ref="formStepOne"
              lazy-validation
            >
              <v-row class="overline">
                <v-col cols="12" md="8">
                  <v-row>
                    <v-col cols="12" class="pb-0">
                      <v-autocomplete
                        v-model="vehicle"
                        :items="options.vehicles"
                        label="Selecciones una Matricula"
                        item-text="matricula"
                        item-value="id"
                        :rules="[
                          (v) => !!vehicle.id || 'Selecciones una Matricula',
                        ]"
                        return-object
                        outlined
                        dense
                      ></v-autocomplete>
                    </v-col>
                    <template v-if="!vehicle.bidon_fuel">
                      <v-col
                        cols="12"
                        md="6"
                        class="d-flex flex-column justify-start align-center"
                      >
                        <div>Ultimo Kilometraje</div>
                        <span class="text-h4">
                          {{
                            vehicle.last_mileage ? vehicle.last_mileage : "----"
                          }}
                          KMS
                        </span>
                      </v-col>
                      <v-col cols="12" md="6" align-self="end">
                        <div class="text-end text-primary">
                          Kilometraje Actual
                        </div>
                        <v-text-field
                          v-model="form.actual_mileage"
                          placeholder="Registrar Kilometraje"
                          type="Number"
                          prefix="KMs"
                          :rules="RulesMileage"
                          clearable
                          outlined
                          reverse
                        ></v-text-field>
                      </v-col>
                    </template>
                    <template v-else>
                      <v-col cols="12" class="pb-0">
                        <v-select
                          v-model="Fuel"
                          :rules="[(v) => !!v || 'Valor Requerido']"
                          :items="options.vehicle_fuel"
                          item-text="name"
                          label="Tipo Combustible"
                          return-object
                          required
                          outlined
                          dense
                        ></v-select>
                      </v-col>
                    </template>
                  </v-row>
                </v-col>
                <v-col
                  cols="12"
                  md="4"
                  class="d-flex flex-column justify-center align-center py-0"
                  v-if="!vehicle.bidon_fuel"
                >
                  <div class="text-center">
                    Marcar del Odometro en la ultima Dispercion
                  </div>
                  <gauge
                    :value="gas_odometer.val"
                    :min="0"
                    :max="100"
                    :fontSize="'16px'"
                    :padding="false"
                    :svgStyle="{
                      maxWidth: 430,
                      maxHeight: 150,
                    }"
                    :activeFill="'#1976D2'"
                    :pointerStroke="'#F44336'"
                    :pivotStroke="'#E91E63'"
                    :pivotFill="'#4CAF50'"
                    :maxThresholdFill="'#4CAF50'"
                    :minThresholdFill="'#F44336'"
                    :maxThreshold="80"
                    :minThreshold="25"
                    :minLabel="'E'"
                    :maxLabel="'F'"
                  />
                  <div style="width: 100%; margin-top: 1rem;">
                    <v-slider
                      v-model="gas_odometer.val"
                      :label="gas_odometer.label"
                      :thumb-color="gas_odometer.color"
                      :rules="RulesOdometer"
                      thumb-label
                      dense
                    ></v-slider>
                  </div>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
          <v-card-actions flat dense>
            <v-spacer> </v-spacer>
            <v-btn text>
              Cancelar
            </v-btn>
            <v-btn color="primary" @click="validStepVehicle()">
              Continuar
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-stepper-content>

      <v-stepper-content step="2" class="pa-0">
        <v-card color="grey lighten-3 overline">
          <v-card-text>
            <form-step-two
              :value="validFormReason"
              :options="options"
              ref="formReason"
            ></form-step-two>
            <!-- <v-form v-model="validFormReason" ref="formReason" lazy-validation>
              <v-row dense align="start">
                <v-col cols="12" md="6" lass="d-flex flex-column">
                  <div class="mb-2 title">Motivo Dispercion:</div>
                  <v-select
                    v-model="form.reason"
                    label="Razon  de Dispercion:"
                    :items="['Solicitud Semanal', 'Viaje', 'Prestamo', 'Otro']"
                    outlined
                    :rules="[(v) => !!v || 'es requerido']"
                    dense
                  ></v-select>
                  <v-textarea
                    v-model="form.reason_description"
                    label="Motivo de Dispercion*"
                    placeholder="Escribir Motivo de Dispercion"
                    counter="255"
                    outlined
                    dense
                  ></v-textarea>
                </v-col>
                <v-col cols="12" md="6" class="d-flex flex-column">
                  <div class="mb-2 title">Con Cargo a:</div>
                  <v-select
                    v-model="form.agency_id"
                    label="Sucursal:"
                    :items="options.agencies"
                    item-value="id"
                    item-text="title"
                    :rules="[(v) => !!v || 'es requerido']"
                    outlined
                    dense
                  ></v-select>
                  <v-select
                    v-model="form.department_id"
                    label="Departamento:"
                    :items="options.departments"
                    item-value="id"
                    item-text="title"
                    :rules="[(v) => !!v || 'es requerido']"
                    outlined
                    dense
                  ></v-select>
                </v-col>
              </v-row>
            </v-form> -->
          </v-card-text>
          <v-card-actions flat dense>
            <v-spacer> </v-spacer>
            <v-btn text>
              Cancel
            </v-btn>
            <v-btn color="primary" @click="validStepReason()">
              Finalizar
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-stepper-content>

      <v-stepper-content step="3" class="pa-0">
        <v-card color="grey lighten-3">
          <v-card-text>
            <v-row class="mt-2 pt-4 overline" justify="center" align="center">
              <v-col cols="12" md="6">
                <v-form v-model="validStepLts" ref="formLts" lazy-validation>
                  <v-text-field
                    v-model="form.fuel_lts"
                    :rules="RulesLts"
                    autofocus
                    outlined
                    dense
                    label="Litros"
                    class="mx-auto text-wrap"
                    @click:append-outer="form.fuel_lts++"
                    @click:prepend="form.fuel_lts--"
                    append-outer-icon="mdi-plus"
                    prepend-icon="mdi-minus"
                    style="max-width: 300px;"
                    :hint="`${vehicle.max_lts_fuel}lts Cantidad Maxima del Tanque`"
                    persistent-hint
                  >
                  </v-text-field>
                </v-form>
              </v-col>
              <v-col
                cols="12"
                md="6"
                class="d-flex flex-column justify-center align-center text-h3"
              >
                {{ form.fuel_lts }} Lts
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions flat dense>
            <v-spacer> </v-spacer>
            <v-btn text>
              Cancel
            </v-btn>
            <v-btn color="primary" @click="validStepLiters()">
              Finalizar
            </v-btn>
          </v-card-actions>
        </v-card>
        <dialog-confirm
          :show="dialog"
          @close="dialog = false"
          @agree="save()"
          max-width="500"
        >
          <template #title>
            Confirmar Solicitud.
          </template>
          <template #body>
            <v-list class="transparent" dense>
              <v-list-item class="overline">
                <v-list-item-title>Vehiculo:</v-list-item-title>
                <v-list-item-subtitle class="text-right caption">
                  <b> {{ `${vehicle.model} ${vehicle.year}` }}</b>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item class="overline">
                <v-list-item-title>Matricula:</v-list-item-title>
                <v-list-item-subtitle class="text-right">
                  <b> {{ vehicle.matricula }}</b>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item class="overline">
                <v-list-item-title>Ticket Card:</v-list-item-title>
                <v-list-item-subtitle class="text-right">
                  <b> {{ vehicle.ticket_card }}</b>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item class="overline">
                <v-list-item-title>Kms Actual:</v-list-item-title>
                <v-list-item-subtitle class="text-right">
                  <b> {{ form.actual_mileage }}Kms</b>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item class="overline">
                <v-list-item-title>Litros Solc.:</v-list-item-title>
                <v-list-item-subtitle class="text-right">
                  <b> {{ form.fuel_lts }} Lts</b>
                </v-list-item-subtitle>
              </v-list-item>
              <v-list-item class="overline">
                <v-list-item-title>Combustible:</v-list-item-title>
                <v-list-item-subtitle class="text-right">
                  <b> {{ vehicle.fuel }}</b>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </template>
        </dialog-confirm>
      </v-stepper-content>
    </v-stepper-items>
  </v-stepper>
</template>
<script>
import DialogConfirm from "@admin/components/DialogConfirm.vue";
import FormStepTwo from "./FormStepTwo.vue";
export default {
  components: { DialogConfirm, FormStepTwo },
  props: {
    propVehicleId: {
      type: [String, Number],
      require: false,
    },
  },
  data() {
    return {
      dialog: false,
      rules: [(v) => !!v || "Es Requerido"],
      vehicle: {
        fuel_odometer: 60,
        fuel_id: null,
      },
      form: {
        actual_mileage: "",
        created_by: "",
        estatus_id: "",
        fuel: "",
        fuel_lts: "",
        fuel_cost_lt: "",
        fuel_odometer: "",
        cost_fuel_lts: "",
        amount: "",
        last_mileage: "",
        reason: "",
        reason_description: "",
        vehicle_id: "",
        agency_id: "",
        department_id: "",
        ticket: "",
      },

      gas_odometer: { label: "Odometro Actual*", val: 50, color: "red" },
      options: {
        vehicles: [],
        agencies: [],
        departments: [],
        vehicle_fuel: [],
      },
      step: 2,
      validFormVehicle: true,
      validFormReason: true,
      validStepLts: true,
    };
  },
  computed: {
    VehicleName() {
      return this.vehicle.id
        ? `${this.vehicle.matricula} - ${this.vehicle.model}`
        : "";
    },
    RulesLts() {
      return [
        (v) =>
          v <= this.vehicle.max_lts_fuel ||
          "Debe ser Menor a la Cantida Maxima del Tanque",
        (v) => v > 0 || "Debes ser Mayor a Cero",
        (v) => !!v || "Es requerido",
      ];
    },
    RulesMileage() {
      return [
        (v) =>
          v > this.vehicle.last_mileage ||
          "Debe ser mayor al Ultimo Kilometraje",
        (v) => !!v || "Es Requerido",
      ];
    },
    RulesOdometer() {
      return [
        (v) =>
          v < this.vehicle.fuel_odometer || "Debe ser Menor al ultimo Odometro",
      ];
    },
    Fuel: {
      get() {
        // const _this = this;
        // return this.vehicles.fuel_id
        //   ? this.options.vehicle_fuel.find(
        //       (e) => (e.id = _this.vehicles.fuel_id)
        //     )
        //   : null;
        return this.vehicle.fuel_id;
      },
      set(value) {
        this.form.fuel = value.name;
        this.form.fuel_cost_lt = value.cost_lt;
      },
    },
  },
  mounted() {
    this.loadOptions(() => {});

    if (this.propVehicleId) {
      this.getVehicle((vehicle) => {
        this.form.vehicle_id = vehicle.id;
      });
    }
  },
  beforeUpdate() {
    this.$store.commit("setBreadcrumbs", [
      { label: "Flotilla", to: { name: "vehicle.list" } },
      { label: "Disperciones", to: { name: "vehicle.dispersal.list" } },
      { label: "Crear" },
    ]);
  },
  methods: {
    validStepVehicle() {
      const _this = this;
      if (!_this.$refs.formStepOne.validate()) return;
      _this.form.fuel_odometer = _this.gas_odometer.val;
      _this.form.vehicle_id = _this.vehicle.id;
      _this.form.last_mileage = _this.vehicle.last_mileage || 0;
      _this.step = 2;
    },
    validStepReason() {
      const _this = this;
      if (!_this.$refs.formReason.$refs.form.validate()) return;
      _this.step = 3;
    },
    validStepLiters() {
      const _this = this;
      if (!_this.$refs.formLts.validate()) return;
      _this.dialog = true;
    },
    async getVehicle(cb) {
      const _this = this;
      await axios
        .get(`/admin/vehicles/${_this.propVehicleId}`)
        .then(function (response) {
          let Response = response.data.data;
          _this.vehicle = Response;
          (cb || Function)(Response);
        });
    },
    async loadOptions(cb) {
      const _this = this;
      await axios
        .get("admin/vehicle-dispersal/create")
        .then(function (response) {
          let Response = response.data.data;
          _this.options.vehicles = Response.vehicles.filter(
            (v) => !!v.can_dispersal
          );
          _this.options.agencies = Response.agencies;
          _this.options.departments = Response.departments;
          _this.options.vehicle_fuel = Response.vehicle_fuel;
          (cb || Function)();
        });
    },
    async save() {
      const _this = this;

      _this.form.fuel = this.Fuel;

      await axios
        .post("/admin/vehicle-dispersal", _this.form)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          _this.$eventBus.$emit("VEHICLE_REFRESH");
          _this.$router.push({ name: "vehicle.dispersal.list" });
        })
        .catch(function (error) {
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
    },
  },
  watch: {
    vehicle: {
      handler: _.debounce(function (v) {
        this.gas_odometer.val = v.fuel_odometer || 100;
      }, 200),
      deep: true,
    },
  },
};
</script>

<style scoped>
.v-progress-circular {
  margin: 1rem;
}
</style>
