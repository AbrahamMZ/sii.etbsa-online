<template>
  <v-layout align-center justify-center row fill-height>
    <v-img
      src="../img/logo-dark.svg"
      class="swiss ma-4"
    ></v-img>
    <!-- <v-img src="../img/etbsa-logo-construccion.png" class="swiss ma-4"></v-img> -->
  </v-layout>
</template>

<script>
export default {
  mounted() {
    const self = this;
    self.$store.commit("setBreadcrumbs", [{ label: "Inicio", name: "" }]);
  },
};
</script>

<style scoped>
.swiss {
  max-width: 650px;
  opacity: 0.2;
}
</style>
