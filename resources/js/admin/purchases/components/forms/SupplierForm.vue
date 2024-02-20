<template>
  <v-form ref="formSupplier" v-model="valid" lazy-validation>
    <v-row dense>
      <v-col cols="12" md="6">
        <v-row class="caption text-uppercase" dense>
          <v-col cols="12" v-if="$gate.allow('activeSupplier', 'compras')">
            <v-switch
              v-model="form.isActive"
              :true-value="1"
              :false-value="0"
              label="Proveedor Activo"
            />
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.code_equip"
              label="Clave en EQUIP:"
              :disabled="!$gate.allow('activeSupplier', 'compras')"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.rfc"
              label="RFC:"
              :rules="[(v) => !!v || 'Es Requerido']"
              counter="15"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="form.business_name"
              :rules="[(v) => !!v || 'Es Requerido']"
              label="Razon Social:"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.phone"
              hint="Numero 10 digitos"
              label="Telefono:"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.email"
              label="Email:"
              type="email"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="12">
            <v-text-field
              v-model="form.address"
              label="Domicilio:"
              outlined
              dense
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
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
          <v-col cols="12" md="6">
            <v-autocomplete
              v-model="form.township_id"
              :items="options.townships"
              label="Municipio"
              item-text="name"
              item-value="id"
              filled
              outlined
              outline
              dense
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-combobox
              v-model="form.giro"
              :items="items"
              label="Giro(s)"
              class="caption"
              deletable-chips
              small-chips
              multiple
              outlined
              filled
              dense
            ></v-combobox>
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="form.credit_days"
              label="Condicion de Pago:"
              :rules="[(v) => !!v || 'Es Requerido']"
              outlined
              :items="[
                { text: 'Requiere Anticipo', value: 1 },
                { text: 'Contado', value: 5 },
                { text: '8 Dias', value: 8 },
                { text: '15 Dias', value: 15 },
                { text: '25 Dias', value: 25 },
                { text: '30 Dias', value: 30 },
                { text: '60 Dias', value: 60 },
                { text: '90 Dias', value: 90 },
              ]"
              dense
            ></v-select>
          </v-col>
          <v-col cols="12" md="4">
            <v-currency-field
              v-model.number="form.credit_limit"
              :default-value="form.credit_limit"
              label="Limite Credito:"
              prefix="$"
              suffix="MXN"
              type="number"
              outlined
              dense
            />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="form.observation"
              label="Observaciones"
              placeholder="Descripción del Proveedor o Nota"
              filled
              outlined
              class="pt-4"
            ></v-textarea>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" md="6">
        <v-expansion-panels multiple>
          <v-expansion-panel>
            <v-expansion-panel-header class="title" color="grey lighten-3">
              Documentacion
            </v-expansion-panel-header>
            <v-expansion-panel-content class="px-0">
              <v-list subheader two-line dense flat>
                <v-subheader inset> Requisitos del Proveedor </v-subheader>
                <supplier-documents-list
                  :documentsList.sync="form.documents"
                ></supplier-documents-list>
              </v-list>
            </v-expansion-panel-content>
          </v-expansion-panel>
          <v-expansion-panel>
            <v-expansion-panel-header class="title" color="grey lighten-3">
              Datos Bancarios
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-card-text class="px-0 pb-0">
                <v-row class="caption text-uppercase" dense>
                  <v-col cols="12">
                    <v-select
                      v-model="form.billing_data.bank"
                      label="Nombre del Banco:"
                      :items="itemsBanks"
                      outlined
                      dense
                    ></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.billing_data.account"
                      label="Cuenta:"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.billing_data.clabe"
                      label="CLABE:"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.billing_data.agency"
                      label="sucursal:"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-expansion-panel-content>
          </v-expansion-panel>
          <v-expansion-panel>
            <v-expansion-panel-header
              class="title"
              color="grey lighten-3"
              ripple
            >
              Contactos
            </v-expansion-panel-header>
            <v-expansion-panel-content>
              <v-simple-table
                v-show="form.contacts && form.contacts.length > 0"
                class="text-uppercase mt-3"
                dense
              >
                <template v-slot:default>
                  <thead>
                    <tr>
                      <th class="text-left">Nombre</th>
                      <th class="text-left">Telefono</th>
                      <th class="text-left">Email</th>
                      <th class="text-left"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in form.contacts" :key="item.name">
                      <td class="caption">{{ item.name }}</td>
                      <td class="caption">{{ item.phone }}</td>
                      <td class="caption">{{ item.email }}</td>
                      <td class="text-right" style="width: 50px">
                        <v-icon color="red" small @click="deleteContact(index)">
                          mdi-delete
                        </v-icon>
                      </td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
              <v-form ref="formContact" lazy-validation>
                <v-row class="overline mt-3" dense>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model="contact.name"
                      label="Nombre contacto (obligatorio)"
                      :rules="[(v) => !!v || 'Es Requerido']"
                      outlined
                      dense
                    ></v-text-field
                  ></v-col>
                  <v-col cols="6" md="4">
                    <v-text-field
                      v-model="contact.phone"
                      label="Telefono (obligatorio)"
                      placeholder="(###)###-##-## Ext: ###"
                      hint="Numero 10 digitos"
                      :rules="[(v) => !!v || 'Es Requerido']"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" md="4">
                    <v-text-field
                      v-model="contact.email"
                      label="Email (opcional)"
                      append-outer-icon="mdi-plus-thick"
                      @click:append-outer="addContact"
                      outlined
                      dense
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { mixinEstates } from "~/common/mixin/estate_township.js";
import SupplierDocumentsList from "../SupplierDocumentsList.vue";
export default {
  components: { SupplierDocumentsList },
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
  data() {
    const defaulFormContact = Object.freeze({
      name: "",
      phone: "",
      email: "",
    });
    return {
      dialogUpload: false,
      settings: [],
      panel: [0],
      contact: Object.assign({}, defaulFormContact),
      rules: {
        required: (value) => !!value || "Requerido.",
        counter: (value) => value.length >= 10 || "Min 10 caracteres",
        email: (value) => {
          const pattern =
            /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return pattern.test(value) || "Email Invalido.";
        },
      },
      select: [],
      items: [
        "Llantas",
        "Papeleria",
        "Motores",
        "Refacciones",
        "Productos Limpieza",
        "Gorras",
        "Botas",
      ],
      itemsBanks: [
        "ABC Capital",
        "Banca Afirme",
        "Banca Mifel",
        "Banco Actinver",
        "Banco Autofin México",
        "Banco Azteca",
        "Banco Bancrea",
        "Banco Bineo",
        "Banco Covalto",
        "Banco Compartamos",
        "Banco Covalto",
        "Banco Credit Suisse (México)",
        "Banco de Inversión Afirme",
        "Banco del Bajío",
        "Banco Forjadores",
        "Banco Inbursa",
        "Banco Invex",
        "Banco Multiva",
        "Banco Regional de Monterrey",
        "Banco Santander",
        "Banco Ve por Más",
        "BanCoppel",
        "Banorte",
        "BBVA México",
        "BNP Paribas",
        "Citibanamex",
        "CIBanco",
        "Consubanco",
        "HSBC México",
        "Intercam Banco",
        "MUFG Bank Mexico",
        "Scotiabank",
      ],
    };
  },

  mounted() {
    const _this = this;
    _this.loadEstates(() => {
      _this.estate_id = _this.Estate;
    });
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
        return this.form.township
          ? (this.estate_id = this.form.township.estate_id)
          : 0;
      },
      set(_estate_id) {
        this.estate_id = _estate_id;
      },
    },
  },
  methods: {
    save() {
      this.$emit("submit");
    },
    addContact() {
      if (!this.$refs.formContact.validate()) return;
      this.form.contacts.push(this.contact);
      this.contact = Object.assign({}, this.defaulFormContact);
      this.$refs.formContact.resetValidation();
    },
    deleteContact(index) {
      this.form.contacts.splice(index, 1);
    },
  },
};
</script>
