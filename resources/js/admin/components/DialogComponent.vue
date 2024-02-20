<template>
  <modal
    :show="show"
    :max-width="maxWidth"
    :closeable="closeable"
    :fullscreen="fullscreen"
    @close="close"
  >
    <v-card flat>
      <v-toolbar color="primary" flat dense>
        <v-btn icon dark @click="close()">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title class="white--text title">
          {{ title }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items class="align-center">
          <slot name="actions"></slot>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text class="pt-6">
        <slot></slot>
      </v-card-text>
      <div style="flex: 1 1 auto"></div>
    </v-card>
  </modal>
</template>

<script>
import Modal from "./Modal";

export default {
  emits: ["close"],
  components: {
    Modal,
  },
  props: {
    show: {
      default: false,
    },
    maxWidth: {
      default: 500,
    },
    closeable: {
      default: false,
    },
    fullscreen: {
      default: false,
    },
    title: {
      type: String,
      default: "Title dialog",
    },
  },
  computed: {
    maxHeight() {
      const height = this.$vuetify.breakpoint.mobile ? "100vh" : "80vh";
      return `calc(${height} - ${this.$vuetify.application.top}px)`;
    },
  },
  methods: {
    close() {
      this.$emit("close");
    },
  },
};
</script>
