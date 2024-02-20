<template>
  <v-card flat>
    <v-card-text>
      <form-customer
        ref="FormCustomer"
        :value="valid"
        :form.sync="form"
      ></form-customer>
    </v-card-text>
    <v-card-actions class="justify-center">
      <v-row dense>
        <v-col cols="6">
          <v-btn color="green" dark block @click="submit">Guardar</v-btn>
        </v-col>
        <v-col cols="6">
          <v-btn text block @click="$emit('cancel')">Cancelar</v-btn>
        </v-col>
      </v-row>
    </v-card-actions>
  </v-card>
</template>
<script>
import FormCustomer from "./FormCustomer.vue";
export default {
  name: "CreateCustomer",
  components: { FormCustomer },
  data() {
    return {
      valid: true,
      form: {
        fiscal_type: "PF",
        full_name: "",
        company: "",
        rfc: "",
        curp: "",
        birthday_date: null,
        phone: "",
        email: "",
        address: "",
        colonia: "",
        code_postal: "",
        town_id: "",
        number_customer: "",
        rating: "A",
        is_moral: false,
      },
    };
  },
  methods: {
    async submit() {
      const _this = this;
      if (!_this.$refs.FormCustomer.$refs.form.validate()) return;
      let payload = {
        ..._this.form,
        is_moral: !!(_this.form.fiscal_type == "PM"),
      };

      await axios
        .post("/admin/customers", payload)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          _this.$emit("cancel");
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
};
</script>
<style></style>
