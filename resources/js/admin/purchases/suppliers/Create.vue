<template>
  <v-card>
    <v-card-title>
      <v-icon class="mr-2">mdi-account-plus</v-icon> Registrar Nuevo Proveedor
      <v-spacer></v-spacer>
    </v-card-title>
    <v-divider class="mb-2"></v-divider>
    <v-card-text>
      <supplier-form v-model="valid" ref="form" :form.sync="supplier">
      </supplier-form>
    </v-card-text>
    <v-card-actions>
      <v-btn
        v-if="$gate.allow('editSupplier', 'compras')"
        color="green"
        :disabled="!valid"
        @click="createSupplier"
        block
        dark
      >
        Guardar
      </v-btn>
    </v-card-actions>
  </v-card>
</template>
<script>
import SupplierForm from "../components/forms/SupplierForm.vue";
export default {
  name: "CreateSupplier",
  components: { SupplierForm },
  data() {
    return {
      valid: true,
      supplier: {
        alias: null,
        business_name: null,
        rfc: null,
        address: null,
        email: null,
        phone: null,
        contact: null,
        billing_data: {
          bank: "",
          account: "",
          clabe: "",
          agency: "",
        },
      },
    };
  },
  mounted() {
    this.$store.commit("setBreadcrumbs", [
      { label: "Proveedores", to: { name: "suppliers.list" } },
      { label: "Crear" },
    ]);
  },
  methods: {
    async createSupplier() {
      if (!this.$refs.form.$refs.formSupplier.validate()) return;
      const _this = this;
      return await axios
        .post("/admin/suppliers", _this.supplier)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          _this.$eventBus.$emit("SUPPLIER_REFRESH");
          // _this.$eventBus.$emit("CLOSE_DIALOG");
        })
        .catch(function (error) {
          _this.$store.commit("hideLoader");
          if (error.response) {
            _this.$store.commit("showSnackbar", {
              message: error.response.data.message,
              color: "error",
              duration: 3000,
            });
            return false;
          } else if (error.request) {
            console.log(error.request);
          } else {
            console.log("Error", error.message);
          }
        });
    },
  },
};
</script>
<style lang=""></style>
