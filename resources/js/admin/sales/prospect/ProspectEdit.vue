<template>
  <prospect-form
    :form.sync="form"
    textBtn="Editar"
    @submit="update()"
  ></prospect-form>
</template>

<script>
import ProspectForm from "./ProspectForm.vue";
export default {
  components: { ProspectForm },
  props: {
    propProspectId: {
      type: [Number, String],
      required: true,
    },
  },
  data() {
    return {
      form: {},
    };
  },
  mounted() {
    const _this = this;
    _this.loadProspect();
  },
  methods: {
    async update() {
      const _this = this;
      await axios
        .put("/admin/prospects/" + _this.propProspectId, _this.form)
        .then(function (response) {
          _this.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          _this.$emit("success");
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
    async loadProspect() {
      const _this = this;
      await axios
        .get(`/admin/prospects/${_this.propProspectId}`)
        .then(function (response) {
          let prospect = response.data.data;
          let estate_id = prospect.township
            ? prospect.township.estate_id
            : null;
          _this.form = {
            ...prospect,
            estate_id: estate_id,
            cultivos:prospect.meta_data?.cultivos
          };
        });
    },
  },
};
</script>
