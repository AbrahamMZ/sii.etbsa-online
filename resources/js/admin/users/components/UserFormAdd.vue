<template>
  <div>
    <v-card>
      <v-card-title> <v-icon>mdi-account</v-icon> Create User </v-card-title>
      <v-divider></v-divider>
      <v-form v-model="valid" ref="userFormAdd" lazy-validation>
        <v-container grid-list-md>
          <v-layout row wrap>
            <v-flex xs12 sm6>
              <v-text-field
                label="First Name"
                v-model="name"
                :rules="nameRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12 sm6>
              <v-text-field
                label="Email"
                v-model="email"
                :rules="emailRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12 sm4>
              <v-autocomplete
                v-model="agency"
                :items="options.agencies"
                item-text="title"
                item-value="id"
                label="Agencia"
                placeholder="Agencia a cual correponde."
              ></v-autocomplete>
            </v-flex>
            <v-flex xs12 sm4>
              <v-autocomplete
                v-model="department"
                :items="options.departments"
                item-text="title"
                item-value="id"
                label="Departamento:"
                placeholder="Departamento a cual correponde."
              ></v-autocomplete>
            </v-flex>
            <v-flex xs12 sm4>
              <v-text-field v-model="jobTitle" label="Puesto"></v-text-field>
            </v-flex>
            <v-flex xs12 sm6>
              <v-text-field
                label="Password"
                type="password"
                v-model="password"
                :rules="passwordRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12 sm6>
              <v-text-field
                label="Confirm Password"
                type="password"
                v-model="passwordConfirm"
                :rules="passwordConfirmRules"
              ></v-text-field>
            </v-flex>
            <v-flex xs12 sm6>
              <v-switch
                label="Pre-Activate Account"
                v-model="active"
              ></v-switch>
            </v-flex>
            <v-flex xs12><v-spacer></v-spacer></v-flex>
            <v-flex xs12>
              <h1 class="title">
                <v-icon>mdi-key</v-icon> Special Permissions
              </h1>
              <v-alert
                color="info"
                icon="mdi-information-outline"
                :value="true"
              >
                Los permisos especiales son permisos exclusivos para este
                usuario. Permisos definidos aquí. Son más superiores que
                cualquier permiso que tenga en su grupo. Así que si el usuario
                pertenece a un grupo que tiene permiso para "hacer algo" pero
                luego se le niega "hacer algo" aquí, al usuario se le negará ese
                permiso. En definitiva, permisos especiales.Tiene alta prioridad
                que los permisos de grupo.
              </v-alert>
              <v-divider></v-divider>
            </v-flex>
            <v-flex xs12 sm4>
              <v-select
                label="Select Permission"
                v-bind:items="options.permissions"
                v-model="selectedPermission"
                item-text="title"
                item-value="key"
              ></v-select>
            </v-flex>
            <v-flex xs12 sm4>
              <v-select
                label="Permission Value"
                v-bind:items="options.permissionValues"
                v-model="selectedPermissionValue"
                item-text="label"
                item-value="value"
              ></v-select>
            </v-flex>
            <v-flex xs12 sm4>
              <v-btn
                @click="addSpecialPermission()"
                class="primary lighten-1"
                dark
              >
                Add Permission
                <v-icon right>mdi-plus</v-icon>
              </v-btn>
            </v-flex>
            <v-flex xs12>
              <div class="permissions_container">
                <v-chip
                  v-for="(p, k) in permissions"
                  :key="k"
                  @click:close="removePermission(k)"
                  class="white--text"
                  :class="{
                    green: p.value == 1,
                    red: p.value == -1,
                    blue: p.value == 0,
                  }"
                >
                  <v-avatar
                    v-if="p.value == -1"
                    class="red darken-4"
                    title="Deny"
                  >
                    <v-icon>mdi-cancel</v-icon>
                  </v-avatar>
                  <v-avatar
                    v-if="p.value == 1"
                    class="green darken-4"
                    title="Allow"
                  >
                    <v-icon>mdi-check-circle</v-icon>
                  </v-avatar>
                  <v-avatar
                    v-if="p.value == 0"
                    class="blue darken-4"
                    title="Inherit"
                  >
                    <v-icon>mdi-swap-horizontal</v-icon>
                  </v-avatar>
                  {{ p.title }}
                </v-chip>
                <div v-if="permissions.length === 0" class="info pa-2">
                  No special permissions assigned.
                </div>
              </div>
            </v-flex>
            <v-flex xs12><v-spacer></v-spacer></v-flex>
            <v-flex xs12>
              <h1 class="title">
                <v-icon>mdi-account-multiple</v-icon> Grupos
              </h1>
              <v-divider></v-divider>
            </v-flex>
            <v-layout wrap mx-2>
              <v-flex xs6 md3 v-for="(g, k) in options.groups" :key="k">
                <v-switch
                  v-bind:label="g.name"
                  v-model="groups[g.id]"
                ></v-switch>
              </v-flex>
            </v-layout>
            <v-flex xs12>
              <v-btn
                block
                @click="save()"
                :disabled="!valid"
                color="primary"
                dark
                >Guardar</v-btn
              >
            </v-flex>
          </v-layout>
        </v-container>
      </v-form>
    </v-card>
  </div>
</template>

<script>
export default {
  data() {
    const self = this;

    return {
      valid: false,
      name: "",
      nameRules: [(v) => !!v || "Name is required"],
      email: "",
      emailRules: [
        (v) => !!v || "E-mail is required",
        (v) =>
          /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) ||
          "E-mail must be valid",
      ],
      password: "",
      passwordRules: [
        (v) => !!v || "Password is required",
        (v) => (v && v.length >= 8) || "Password must be atleast 8 characters.",
      ],
      passwordConfirm: "",
      passwordConfirmRules: [
        (v) => !(v !== self.password) || "Password do not match.",
      ],
      permissions: [],
      groups: [],
      agency: null,
      department: null,
      jobTitle: "",
      active: "",
      options: {
        permissions: [],
        permissionValues: [
          { label: "Allow", value: 1 },
          { label: "Deny", value: -1 },
          { label: "Inherit", value: 0 },
        ],
        groups: [],
        agencies: [],
        departments: [],
      },
      selectedPermission: {},
      selectedPermissionValue: 0,

      alert: {
        show: false,
        icon: "",
        color: "",
        message: "",
      },
    };
  },
  mounted() {
    console.log("components.UserFormAdd.vue");

    const self = this;

    self.loadPermissions((cb) => {});
    self.loadGroups((cb) => {});

    self.$store.commit("setBreadcrumbs", [
      { label: "Users", to: { name: "users.list" } },
      { label: "Create", to: "" },
    ]);
  },
  methods: {
    removePermission(i) {
      const self = this;

      self.permissions.splice(i, 1);
    },
    save() {
      const self = this;

      let payload = {
        name: self.name,
        email: self.email,
        password: self.password,
        active: self.active ? moment().format("YYYY-MM-DD") : null,
        permissions: self.permissions,
        groups: self.groups,
        agency_id: self.agency,
        departments_id: self.department,
        job_title: self.jobTitle
      };

      self.$store.commit("showLoader");

      axios
        .post("/admin/users", payload)
        .then(function(response) {
          self.$store.commit("showSnackbar", {
            message: response.data.message,
            color: "success",
            duration: 3000,
          });

          self.$eventBus.$emit("USER_ADDED");
          self.$store.commit("hideLoader");

          // reset
          self.$refs.userFormAdd.reset();
          self.permissions = [];
        })
        .catch(function(error) {
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
    addSpecialPermission() {
      const self = this;

      _.each(self.options.permissions, (p) => {
        if (self.selectedPermission === p.key) {
          if (!self.existsInPermissions(self.selectedPermission)) {
            p.value = self.selectedPermissionValue;
            self.permissions.push(p);
          }
        }
      });
    },
    existsInPermissions(permissionKey) {
      const self = this;
      let found = false;
      _.each(self.permissions, (p) => {
        if (p.key === permissionKey) found = true;
      });
      return found;
    },
    loadPermissions(cb) {
      const self = this;

      let params = {
        paginate: "no",
      };

      axios
        .get("/admin/permissions", { params: params })
        .then(function(response) {
          self.options.permissions = response.data.data;
          cb();
        });
    },
    loadGroups(cb) {
      const self = this;

      let params = {
        paginate: "no",
      };

      axios.get("/admin/groups", { params: params }).then(function(response) {
        self.options.groups = response.data.data;

        _.each(self.options.groups, (g) => {
          g.selected = false;
        });

        cb();
      });

      axios
        .get("/admin/tracking/sales_history/resources")
        .then(function(response) {
          let Data = response.data.data;
          self.options.agencies = Data.agencies;
          self.options.departments = Data.departments;
          (cb || Function)();
        });
    },
  },
};
</script>
