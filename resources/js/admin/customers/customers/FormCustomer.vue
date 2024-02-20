<template>
  <v-form v-model="valid" ref="form" lazy-validation>
    <v-row dense class="text-uppercase">
      <v-col cols="12">
        <v-radio-group v-model="form.fiscal_type" row dense>
          <v-radio label="Persona Fisica" value="PF"></v-radio>
          <v-radio label="Persona Moral" value="PM"></v-radio>
        </v-radio-group>
      </v-col>
      <v-col cols="12">
        <v-text-field
          v-model="form.full_name"
          label="Denominacion / Razon Social:"
          hint="Debe se al de la Contancia de Situacion Fiscal"
          persistent-hint
          :rules="[(v) => !!v || 'El Nombre es requerido']"
          outlined
          dense
        >
        </v-text-field>
      </v-col>
      <!-- <v-col cols="12">
        <v-text-field
          v-model="form.company"
          label="Compañia/Razon Social"
          hint="es requerido"
          outlined
          dense
        >
        </v-text-field>
      </v-col> -->

      <v-col cols="6">
        <v-text-field
          v-model="form.rfc"
          label="RFC"
          hint="es requerido"
          outlined
          dense
        >
        </v-text-field>
      </v-col>
      <v-col cols="6">
        <v-text-field
          v-model="form.curp"
          label="CURP"
          hint="es requerido"
          outlined
          dense
        >
        </v-text-field>
      </v-col>
      <!-- <v-col cols="12">
        <v-menu
          ref="menu"
          v-model="menu"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
          min-width="auto"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-text-field
              v-model="form.birthday_date"
              label="Fecha Nacimiento"
              prepend-icon="mdi-calendar"
              readonly
              dense
              outlined
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="form.birthday_date"
            :active-picker.sync="activePicker"
            :max="
              new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
                .toISOString()
                .substr(0, 10)
            "
            min="1921-01-01"
            @change="save"
          ></v-date-picker>
        </v-menu>
      </v-col> -->
      <v-col cols="6">
        <v-text-field
          v-model="form.phone"
          label="Telefono"
          hint="es requerido"
          outlined
          dense
        ></v-text-field>
      </v-col>
      <v-col cols="6">
        <v-text-field
          v-model="form.email"
          label="Correo Electronico"
          hint="es requerido"
          outlined
          dense
        ></v-text-field>
      </v-col>
      <v-col cols="12">
        <v-textarea
          v-model="form.address"
          label="Direccion"
          dense
          outlined
          filled
        ></v-textarea>
      </v-col>
      <v-col cols="8">
        <v-text-field
          v-model="form.colonia"
          label="Colonia"
          dense
          outlined
        ></v-text-field>
      </v-col>
      <v-col cols="4">
        <v-text-field
          v-model="form.code_postal"
          label="Codigo Postal"
          dense
          outlined
        ></v-text-field>
      </v-col>
      <v-col cols="6">
        <v-autocomplete
          v-model="Estate"
          :items="options.estates"
          item-text="name"
          item-value="id"
          label="Estado:"
          filled
          outlined
          dense
        />
      </v-col>
      <v-col cols="6">
        <v-autocomplete
          v-model="form.town_id"
          :items="options.townships"
          label="Municipio"
          item-text="name"
          item-value="id"
          :rules="[(v) => !!v || 'El municipio es requerido']"
          filled
          outlined
          outline
          dense
        />
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
    </v-row>
  </v-form>
</template>
<script>
import { mixinEstates } from "~/common/mixin/estate_township.js";
export default {
  name: "FormCustomer",
  mixins: [mixinEstates],
  props: {
    value: {
      type: Boolean,
      default: true,
    },
    form: {
      require: true,
      type: Object,
      default: () => {
        return {};
      },
    },
  },

  mounted() {
    const _this = this;
    _this.loadOptions();
    _this.loadEstates(() => {
      _this.estate_id = _this.Estate;
    });
  },
  data() {
    return {
      menu: false,
      activePicker: null,
      options: {},
    };
  },
  computed: {
    valid: {
      get() {
        return this.value;
      },
      set(value) {
        this.$emit("input", value);
      },
    },
    Estate: {
      get() {
        return !!this.form.town_id && this.form.township
          ? (this.estate_id = this.form.township.estate_id)
          : 0;
      },
      set(_estate) {
        this.estate_id = _estate;
      },
    },
  },
  methods: {
    save(date) {
      this.$refs.menu.save(date);
    },
    loadOptions() {
      const _this = this;
      // axios.get("/admin/employees/create").then((res) => {
      //   _this.options.agencies = res.data.data.agencies;
      // });
    },
  },
  watch: {
    menu(val) {
      val && setTimeout(() => (this.activePicker = "YEAR"));
    },
  },
};
</script>
<style></style>
