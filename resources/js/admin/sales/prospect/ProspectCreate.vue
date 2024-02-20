<template>
  <prospect-form :form.sync="form" @submit="Save()"></prospect-form>
</template>

<script>
// import FormProspect from "./forms/Prospect";
import ProspectForm from "./ProspectForm.vue";
export default {
  components: { ProspectForm },
  data() {
    return {
      form: {
        rfc: null,
        town: null,
        email: null,
        phone: null,
        company: null,
        cultivos: [],
        customer_id: null,
        full_name: null,
        is_moral: 0,
        estate_id: null,
        township_id: null,
      },
    };
  },
  methods: {
    async Save() {
      const _this = this;
      await axios
        .post("/admin/prospects", _this.form)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          let {
            prospect: { id },
          } = response.data.data;
          _this.$emit("success", id);
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
    },
  },
};
</script>
