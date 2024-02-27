<template>
  <v-card>
    <v-card-title> Registrar Ingreso </v-card-title>
    <v-card-text>
      <ingreso-form :editedItem.sync="form"></ingreso-form>
    </v-card-text>

    <v-card-actions>
      <v-btn color="blue darken-1" block dark @click="save"> Guardar </v-btn>
    </v-card-actions>
  </v-card>
</template>
<script>
import IngresoForm from "../IngresoForm.vue";
export default {
  components: { IngresoForm },
  props: ["propTipoPoliza"],
  data: () => {
    return {
      form: {
        // external_id: null,
        reference_number: null,
        reference_name: null,
        reference_concept: null,
        description: null,
        amount: 0,
        currency_id: 1,
        payment_source_id: 1,
        tipo_poliza_id: 1,
        category_id: null,
        // origen_bank_accounts_id: null,
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
        tipo_poliza_id: 1,
        category_id: null,
        // origen_bank_accounts_id: null,
        apply_bank_accounts_id: null,
        is_applied: false,
        // apply_date: null,
        // user_created: null,
        // user_updated: null,
      },
    };
  },
  methods: {
    async save() {
      const _this = this;

      try {
        let {
          data: { message },
        } = await axios.post(`/admin/polizas`, _this.form);

        _this.$store.commit("showSnackbar", {
          message: message,
          color: "success",
          duration: 3000,
        });

        this.$nextTick(() => {
          this.form = Object.assign({}, this.defaultItem);
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
<style>
</style>