<template>
  <div class="component-wrap">
    <!-- form -->
    <v-card>
      <v-card-title> <v-icon left>mdi-key</v-icon> Create Permission </v-card-title>
      <v-divider class="mb-2"></v-divider>
      <v-form v-model="valid" ref="permissionFormAdd" lazy-validation>
        <v-container grid-list-md>
          <v-layout row wrap>
            <v-flex xs12>
              <div class="body-2 white--text">Permission Details</div>
            </v-flex>
            <v-flex xs12>
              <v-text-field
                label="Permission Title"
                v-model="title"
                :rules="titleRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12>
              <v-text-field
                label="Permission Key"
                v-model="permissionKey"
                :rules="permissionKeyRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12>
              <v-textarea
                label="Description"
                v-model="description"
                :rules="descriptionRules"
              ></v-textarea>
            </v-flex>
            <v-flex xs12>
              <v-btn
                @click="save()"
                :loading="isLoading"
                :disabled="!valid || isLoading"
                color="primary"
                >Save</v-btn
              >
            </v-flex>
          </v-layout>
        </v-container>
      </v-form>
    </v-card>
    <!-- /form -->
  </div>
</template>

<script>
export default {
  data() {
    return {
      valid: false,
      isLoading: false,
      title: "",
      titleRules: [(v) => !!v || "Title is required"],
      description: "",
      descriptionRules: [(v) => !!v || "Description is required"],
      permissionKey: "",
      permissionKeyRules: [
        (v) => !!v || "Permission Key is required",
        (v) =>
          (v && !v.match(/[^\w\.]+/g)) ||
          "Description cannot contain special characters",
      ],
    };
  },
  mounted() {
    this.$store.commit("setBreadcrumbs", [
      { label: "Users", to: { name: "users.list" } },
      { label: "Permissions", to: { name: "users.permissions.list" } },
      { label: "Create", name: "" },
    ]);
  },
  watch: {
    permissionKey(v) {
      if (v) this.permissionKey = v.replace(" ", ".").toLowerCase();
    },
    title(v) {
      if (v) this.permissionKey = v.replace(" ", ".").toLowerCase();
    },
  },
  methods: {
    save() {
      const self = this;

      let payload = {
        title: self.title,
        description: self.description,
        key: self.permissionKey,
      };

      self.isLoading = true;

      axios
        .post("/admin/permissions", payload)
        .then(function (response) {
          self.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });
          self.$eventBus.$emit("PERMISSION_ADDED");

          // reset
          self.$refs.permissionFormAdd.reset();
          self.permissions = [];
          self.isLoading = false;
        })
        .catch(function (error) {
          self.isLoading = false;
          self.$store.commit("hideLoader");

          if (error.response) {
            self.$store.commit("showSnackbar", {
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
