<template>
  <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="submit()">
    <v-row align="start" align-content="start" dense>
      <v-col cols="12">
        <v-text-field
          v-model="form.full_name"
          label="Nombre del Contacto *"
          placeholder="Nombre completo"
          persistent-placeholder
          :rules="rules"
          outlined
          filled
          dense
        />
      </v-col>
      <v-col cols="12">
        <v-text-field v-model="form.rfc" label="RFC" filled dense outlined />
      </v-col>

      <v-col cols="12" md="6">
        <v-text-field
          v-model="form.email"
          label="Correo Electronico"
          type="email"
          outlined
          filled
          dense
        />
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field
          v-model="form.phone"
          label="Telefono *"
          counter="10"
          outlined
          filled
          dense
        />
      </v-col>
      <v-col cols="12">
        <v-textarea
          v-model="form.town"
          label="Domicilio:"
          placeholder="(Comunidad o Rancho)"
          height="100"
          outlined
          filled
          dense
        />
      </v-col>
      <v-col cols="12" md="6">
        <v-autocomplete
          v-model="form.estate_id"
          :items="options.estates"
          item-text="name"
          item-value="id"
          label="Estado *"
          outlined
          filled
          dense
        />
      </v-col>

      <v-col cols="12" md="6">
        <v-autocomplete
          v-model="form.township_id"
          :items="options.townships"
          label="Municipio *"
          item-text="name"
          item-value="id"
          outlined
          filled
          dense
        />
      </v-col>
      <v-col cols="12" md="6">
        <v-select
          v-model="form.segmentacion"
          :items="['Chico', 'Mediano', 'Grande', 'Agroindustrial', 'Jardinero']"
          label="Segmentacion Cliente"
          outlined
          filled
          dense
        ></v-select>
      </v-col>
      <v-col cols="12" md="6">
        <v-select
          v-model="form.capacidad_tech"
          :items="['Baja', 'Mediana', 'Alta', 'Experto']"
          label="Capacidad de Tecnologia"
          outlined
          filled
          dense
        ></v-select>
      </v-col>
      <v-col cols="12" md="6">
        <v-select
          v-model="form.rating"
          :items="['AAA', 'AA', 'A', 'Lista Negra']"
          label="Calificacion"
          outlined
          filled
          dense
        ></v-select>
      </v-col>
      <v-col cols="12" md="6">
        <v-select
          v-model="form.tactica_jd"
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
          clearable
        ></v-select>
      </v-col>
      <v-col cols="12">
        <v-combobox
          v-model="form.cultivos"
          label="Cultivos"
          :items="cultivos"
          multiple
          chips
          deletable-chips
          outlined
          hint="Lo que usualmente cultiva"
          persistent-hint
          clearable
        ></v-combobox>
      </v-col>
      <v-col cols="12">
        <v-autocomplete
          v-model="form.customer_id"
          :items="formOptions.customers"
          label="Cliente / Compañia"
          item-text="full_name"
          item-value="id"
          outlined
          filled
          dense
          clearable
        >
          <template v-slot:prepend-item>
            <v-btn color="green" class="ma-2" dark block @click="dialog = true">
              Registrar Cliente/Organizacion
              <v-icon right>mdi-account-plus </v-icon>
            </v-btn>
          </template>

          <template #item="{ item }">
            <v-list-item-content>
              <v-list-item-title class="text-uppercase font-weight-bold">
                {{ item.full_name }}
              </v-list-item-title>
              <v-list-item-subtitle> RFC: {{ item.rfc }} </v-list-item-subtitle>
            </v-list-item-content>
          </template>
        </v-autocomplete>
      </v-col>
    </v-row>

    <v-btn block type="submit" color="primary" :disabled="!valid">
      {{ textBtn }}
    </v-btn>

    <dialog-component
      :show="dialog"
      @close="dialog = false"
      :fullscreen="$vuetify.breakpoint.mobile"
      title="Registrar Nuevo Cliente"
      :maxWidth="600"
      closeable
      key="createCustomer"
    >
      <create-customer
        @cancel="
          dialog = false;
          getFormOptions();
        "
      ></create-customer>
    </dialog-component>
  </v-form>
</template>
<script>
import { mixinEstates } from "~/common/mixin/estate_township.js";
import DialogComponent from "@admin/components/DialogComponent.vue";
import CreateCustomer from "@admin/customers/customers/Create.vue";
export default {
  mixins: [mixinEstates],
  components: { DialogComponent, CreateCustomer },
  props: {
    form: {
      type: Object,
      required: true,
      default: () => {
        return {};
      },
    },
    textBtn: {
      type: String,
      required: false,
      default: "Guardar",
    },
  },
  data() {
    return {
      valid: true,
      dialog: false,
      rules: [(v) => !!v || "campo requerido."],
      formOptions: {
        customers: [],
      },
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
    };
  },
  mounted() {
    const _this = this;
    _this.loadEstates(() => {
      _this.estate_id = _this.form.estate_id ? _this.form.estate_id : null;
    });
    _this.getFormOptions();
  },
  methods: {
    submit() {
      if (!this.$refs.form.validate()) return;
      this.$eventBus.$emit("PROSPECT-FORM-SUBMIT");
      this.$emit("submit");
    },
    async getFormOptions() {
      let res = await axios.get("/admin/options/prospects");
      this.formOptions = res.data.data.options;
    },
  },
};
</script>
<style></style>
