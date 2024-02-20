<template >
  <v-card>
    <v-expansion-panels>
      <v-expansion-panel>
        <v-expansion-panel-header>
          <span class="title">Filtro Avanzado</span>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
          <v-row
            class="d-flex align-content-start flex-wrap text-uppercase"
            color="grey lighten-2"
            no-gutters
          >
            <v-col cols="12" md="6" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-combobox
                v-model="filters.clave_cliente"
                label="CLIENTE"
                :items="options.Clientes"
                placeholder="Seleccionar Cliente"
                :filter="customFilterCliente"
                item-value="Clave_Cliente"
                item-text="Clave_Cliente"
                deletable-chips
                hide-details
                small-chips
                clearable
                multiple
                outlined
                dense
                chips
              >
                <template #item="{ item }">
                  <v-list-item-content>
                    <v-list-item-subtitle>
                      {{ item.Clave_Cliente }}
                    </v-list-item-subtitle>
                    <v-list-item-title> {{ item.Cliente }} </v-list-item-title>
                  </v-list-item-content>
                </template>
              </v-combobox>
            </v-col>
            <v-col cols="12" md="6" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-combobox
                v-model="filters.clave_vendedor"
                label="VENDEDOR"
                :items="options.Vendedores"
                item-text="ClaveVendedor"
                item-value="ClaveVendedor"
                placeholder="Buscar Clave Vendedor, Nombre Vendedor"
                :filter="customFilterVendedor"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                small-chips
                deletable-chips
              >
                <template #item="{ item }">
                  <v-list-item-content>
                    <v-list-item-subtitle>
                      {{ item.ClaveVendedor }}
                    </v-list-item-subtitle>
                    <v-list-item-title> {{ item.Nombre }} </v-list-item-title>
                  </v-list-item-content>
                </template>
              </v-combobox>
            </v-col>
            <v-col cols="12" md="3" class="pa-2 flex-grow-1 flex-shrink-0">
              <!-- item-text="nombre"
              item-value="id" -->
              <v-select
                v-model="filters.sucursales"
                label="SUCURSAL"
                :items="options.Sucursales"
                placeholder="Seleccionar Sucursal"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                small-chips
                deletable-chips
              >
                <!-- <template v-slot:selection="{ item }">
                  <v-list-item-content>
                    <v-list-item-subtitle>
                      {{ item.linea }}
                    </v-list-item-subtitle>
                    <v-list-item-title> {{ item.nombre }} </v-list-item-title>
                  </v-list-item-content>
                </template>
                <template #item="{ item }">
                  <v-list-item-content>
                    <v-list-item-subtitle>
                      {{ item.linea }}
                    </v-list-item-subtitle>
                    <v-list-item-title> {{ item.nombre }} </v-list-item-title>
                  </v-list-item-content>
                </template> -->
              </v-select>
            </v-col>
            <!--  -->
            <!-- <v-col cols="12" md="3" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-select
                v-model="filters.lineas"
                label="Linea"
                :items="options.Linea"
                item-text="linea"
                item-value="linea"
                placeholder="Seleccionar Linea"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
                small-chips
              >
              </v-select>
            </v-col> -->
            <v-col cols="12" md="2" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-select
                v-model="filters.years"
                label="AÑO(s)"
                :items="OptionsYears"
                item-text="year"
                item-value="year"
                placeholder="Seleccionar Año"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
                small-chips
              >
              </v-select>
            </v-col>
            <!-- <v-col cols="12" md="2" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-select
                v-model="filters.currency"
                label="MONEDA"
                :items="options.Moneda"
                item-text="MONEDA"
                item-value="MONEDA"
                placeholder="Seleccionar Moneda"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
                small-chips
              >
              </v-select>
            </v-col> -->
            <!-- <v-col cols="12" md="2" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-select
                v-model="filters.tipo_venta"
                label="TIPO DE VENTA"
                :items="options.TipoVenta"
                item-text="descripcion"
                item-value="clave"
                placeholder="Seleccionar Tipo de Venta"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
              >
                <template v-slot:selection="{ item }">
                  <v-list-item-content class="d-flex">
                    <v-list-item-subtitle>
                      {{ item.clave }}
                    </v-list-item-subtitle>
                    <v-list-item-title>
                      {{ item.descripcion }}
                    </v-list-item-title>
                  </v-list-item-content>
                </template>
                <template #item="{ item }">
                  <v-list-item-content>
                    <v-list-item-subtitle>
                      {{ item.clave }}
                    </v-list-item-subtitle>
                    <v-list-item-title>
                      {{ item.descripcion }}
                    </v-list-item-title>
                  </v-list-item-content>
                </template>
              </v-select>
            </v-col> -->
            <!-- <v-col cols="12" md="3" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-combobox
                v-model="filters.municipio"
                label="Municipio"
                :items="options.Municipio"
                placeholder="Buscar Municipio"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
                small-chips
              >
              </v-combobox>
            </v-col> -->
            <!-- <v-col cols="12" md="3" class="pa-2 flex-grow-1 flex-shrink-0">
              <v-combobox
                v-model="filters.estado"
                label="Estado"
                :items="options.Estado"
                placeholder="Buscar Estado"
                clearable
                outlined
                hide-details
                dense
                multiple
                chips
                deletable-chips
                small-chips
              >
              </v-combobox>
            </v-col> -->
          </v-row>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-data-table
      :headers="headers"
      :items="items"
      :options.sync="pagination"
      :server-items-length="totalItems"
      fixed-header
      caption
      dense
      class="elevation-3"
      calculate-widths
      :footer-props="{
        showCurrentPage: true,
        showFirstLastPage: true,
        firstIcon: 'mdi-arrow-collapse-left',
        lastIcon: 'mdi-arrow-collapse-right',
        prevIcon: 'mdi-minus',
        nextIcon: 'mdi-plus',
        itemsPerPageOptions: [5, 10, 15, 100, 200, 500, 2000, 5000],
      }"
    >
      <template #top>
        <v-toolbar flat>
          <v-toolbar-title> Facturacion Maquinaria</v-toolbar-title>
          <v-spacer />
          <!-- <v-text-field
            v-model="searchCustomer"
            label="Nombre Cliente"
            placeholder="Nombre, Compañia"
            persistent-placeholder
            outlined
            hide-details
            clearable
            dense
            class="mr-2"
          /> -->
          <v-text-field
            v-model="search"
            label="Buscar"
            placeholder="Nombre Producto, Serie Producto, MODELO"
            persistent-placeholder
            outlined
            hide-details
            clearable
            dense
          />
          <v-btn icon @click="cleanFilter" class="mr-2">
            <v-icon color="red">mdi-filter-remove-outline</v-icon>
          </v-btn>
          <!-- <v-btn
            class="mr-2"
            @click="exportToCsv('sales_customers.xlsx', items)"
            color="grey"
            dark

          >
            Exportar
            <v-icon right color="green">mdi-file-excel</v-icon>
          </v-btn> -->
          <v-btn @click="searchBtn()" dark>
            Buscar
            <v-icon right color="blue">mdi-magnify</v-icon>
          </v-btn>
        </v-toolbar>
      </template>
      <template v-slot:[`item.sucursal`]="{ item }">
        <div class="caption text--secondary">
          {{ item.linea }}
        </div>
        <div class="text-uppercase font-weight-bold">{{ item.sucursal }}</div>
      </template>
      <template v-slot:[`item.cliente`]="{ item }">
        <div class="caption text--secondary">
          Clave: {{ item.clave_cliente }}
        </div>
        <div class="text-uppercase font-weight-bold">
          {{ item.cliente }}
        </div>
      </template>
      <!-- <template v-slot:[`item.Estado`]="{ item, value }">
        <div class="text-uppercase font-weight-bold">{{ value }}</div>
        <div class="caption text--secondary">
          {{ item.Municipio }}
        </div>
      </template> -->
      <template v-slot:[`item.nombre_vendedor`]="{ item }">
        <div class="caption text--secondary">
          Clave: {{ item.clave_vendedor }}
        </div>
        <div class="text-uppercase font-weight-bold">
          {{ item.nombre_vendedor }}
        </div>
      </template>
      <template v-slot:[`item.tipo_venta`]="{ item }">
        <div class="caption text--secondary">
          <v-chip x-small dark color="green darken-4" label outlined>
            {{ `${item.tipo_venta}-${item.tipo_venta_nombre}` }}
          </v-chip>
        </div>
        <div class="text-uppercase font-weight-bold">
          {{ item.producto }}
        </div>
      </template>

      <template v-slot:[`item.NIP`]="{ value }">
        <div style="max-width: 200px">
          <v-chip label outlined color="black" class="font-weight-bold" dark>
            {{ value }}
          </v-chip>
        </div>
      </template>
      <template v-slot:[`item.invoice_date`]="{ value }">
        <v-chip label outlined color="blue" class="font-weight-bold" dark>
          {{ value }}
        </v-chip>
      </template>
      <template v-slot:[`item.precio_venta`]="{ value }">
        <v-chip outlined label color="primary" class="font-weight-bold" dark>
          {{ value | currency }} MX
          <!-- {{ item.currency }} -->
        </v-chip>
      </template>
      <template #footer>
        <v-toolbar flat>
          <v-toolbar-title class="text-left">
            Ultima Carga: {{ lastUpdated }}
          </v-toolbar-title>
          <v-spacer />
          <v-toolbar-title class="text-right">
            Total (MXN):{{ sumTotalVentasAg | currency }}
            <!-- <v-divider inset/>
            Total (MXN):{{ sumTotalVentasAgMX | currency }} -->
          </v-toolbar-title>
        </v-toolbar>
      </template>
    </v-data-table>

    <v-card-text v-if="ventasPorCliente.length > 0">
      <div class="text-h3">Resumen Cliente(s)</div>
      <v-row dense>
        <v-col cols="12" md="6">
          <v-simple-table class="elevation-4" height="500" fixed-header>
            <template #default>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Tipo de Venta</th>
                  <th>Total Unidades</th>
                  <th>Total Venta</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(venta, index) in ventasPorCliente"
                  :key="`${index}-${venta.tipo_venta}`"
                >
                  <td>
                    <div class="text-secondary">{{ venta.clave_cliente }}</div>
                    {{ venta.cliente }}
                  </td>
                  <td class="text-no-wrap">
                    <div class="font-weight-bold">{{ venta.tipo }}</div>
                    {{ venta.tipo_venta || "Otro" }}
                  </td>
                  <td>{{ venta.count }}</td>
                  <td>{{ venta.total_comprado | currency }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
        <v-col cols="12" md="6">
          <pie-chart
            :data-labels="dataLabels"
            :data-values="dataValues"
            :height="500"
          />
        </v-col>
        <v-col cols="12" md="6">
          <bar-chart :data-set="BarDataSet" :height="500"></bar-chart>
        </v-col>
        <v-col cols="12" md="6">
          <v-card flat>
            <v-card-title> Ultima Compra Cliente </v-card-title>
            <v-card-text>
              <v-simple-table class="elevation-4" max-height="500" fixed-header>
                <template #default>
                  <thead>
                    <tr>
                      <th>Cliente</th>
                      <th>Sucursal</th>
                      <th>Producto</th>
                      <th>Precio Venta</th>
                      <th>Tipo Venta</th>
                      <th>Vendedor</th>
                      <th>Direcciones</th>
                      <th>Teléfonos</th>
                      <th>Fecha Factura</th>
                      <th>Año</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(item, key) in ultimasInvoicePorCliente"
                      :key="key"
                    >
                      <td>
                        <div class="text-secondary">
                          {{ item.clave_cliente }}
                        </div>
                        {{ item.cliente }}
                      </td>
                      <td>{{ item.sucursal }}</td>
                      <td>
                        <div>{{ item.NIP }}</div>
                        {{ item.producto }}
                      </td>
                      <td>{{ item.precio_venta | currency }}</td>
                      <td class="text-no-wrap">
                        <div class="font-weight-bold">{{ item.tipo }}</div>
                        {{ item.tipo_venta || "Otro" }}
                      </td>
                      <td>
                        <div>{{ item.clave_vendedor }}</div>
                        {{ item.nombre_vendedor }}
                      </td>
                      <td>{{ item.addresses }}</td>
                      <td>{{ item.phones }}</td>
                      <td>{{ item.invoice_date }}</td>
                      <td>{{ item.year }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>
    <!-- <v-card-text v-if="ventasPorVendedor.length > 0"> -->
    <v-card-text v-if="ventasPorVendedor.length > 0">
      <div class="text-h3">Resumen Vendedor(es)</div>
      <v-row dense>
        <v-col cols="12" md="6">
          <v-simple-table class="elevation-4" height="500" fixed-header>
            <template #default>
              <thead>
                <tr>
                  <th>Vendedor</th>
                  <th>Tipo de Venta</th>
                  <th>Total Unidades</th>
                  <th>Total Venta</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(venta, index) in ventasPorVendedor"
                  :key="`${index}-${venta.tipo_venta}`"
                >
                  <td>
                    <div class="text-secondary">{{ venta.clave_vendedor }}</div>
                    {{ venta.vendedor }}
                  </td>
                  <td class="text-no-wrap">
                    <div class="font-weight-bold">{{ venta.tipo }}</div>
                    {{ venta.tipo_venta || "Otro" }}
                  </td>
                  <td class="text-center">{{ venta.count }}</td>
                  <td>{{ venta.total_comprado | currency }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
        <v-col cols="12" md="6">
          <pie-chart
            :data-labels="dataVendedorLabels"
            :data-values="dataVendedorValues"
            :height="500"
          />
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Acumulado Venta por Año </v-card-title>
            <v-card-text>
              <bar-chart
                :data-set="BarDataVendedorSet"
                :height="500"
              ></bar-chart>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Acumulado Tractores por Año </v-card-title>
            <v-card-text>
              <bar-chart
                :data-set="BarDataTraVendedorSet"
                :height="500"
              ></bar-chart>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Ultima Venta Vendedor </v-card-title>
            <v-card-text>
              <v-simple-table class="elevation-4" max-height="500" fixed-header>
                <template #default>
                  <thead>
                    <tr>
                      <th>Cliente</th>
                      <th>Sucursal</th>
                      <th>Producto</th>
                      <th>Precio Venta</th>
                      <th>Tipo Venta</th>
                      <th>Vendedor</th>
                      <th>Direcciones</th>
                      <th>Teléfonos</th>
                      <th>Fecha Factura</th>
                      <th>Año</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(item, key) in ultimasInvoiceVendedor"
                      :key="key"
                    >
                      <td>
                        <div class="text-secondary">
                          {{ item.clave_cliente }}
                        </div>
                        {{ item.cliente }}
                      </td>
                      <td>{{ item.sucursal }}</td>
                      <td>
                        <div>{{ item.NIP }}</div>
                        {{ item.producto }}
                      </td>
                      <td>{{ item.precio_venta | currency }}</td>
                      <td class="text-no-wrap">
                        <div class="font-weight-bold">{{ item.tipo }}</div>
                        {{ item.tipo_venta || "Otro" }}
                      </td>
                      <td>
                        <div>{{ item.clave_vendedor }}</div>
                        {{ item.nombre_vendedor }}
                      </td>
                      <td>{{ item.addresses }}</td>
                      <td>{{ item.phones }}</td>
                      <td>{{ item.invoice_date }}</td>
                      <td>{{ item.year }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>
    <v-card-text v-if="ventasPorSucursal.length > 0">
      <div class="text-h3">Resumen Sucursal(es)</div>
      <v-row dense>
        <v-col cols="12" md="6">
          <v-simple-table class="elevation-4" height="500" fixed-header>
            <template #default>
              <thead>
                <tr>
                  <th>Sucursal</th>
                  <th>Tipo de Venta</th>
                  <th>Total Unidades</th>
                  <th>Total Venta</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(venta, index) in ventasPorSucursal"
                  :key="`${index}-${venta.tipo_venta}`"
                >
                  <td>
                    <div class="text-secondary">{{ venta.sucursal }}</div>
                    {{ venta.vendedor }}
                  </td>
                  <td class="text-no-wrap">
                    <div class="font-weight-bold">{{ venta.tipo }}</div>
                    {{ venta.tipo_venta || "Otro" }}
                  </td>
                  <td>{{ venta.count }}</td>
                  <td>{{ venta.total_comprado | currency }}</td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
        <v-col cols="12" md="6">
          <pie-chart
            :data-labels="dataSucursalLabels"
            :data-values="dataSucursalValues"
            :height="500"
          />
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Acumulado Venta por Año </v-card-title>
            <v-card-text>
              <bar-chart
                :data-set="BarDataSucursalSet"
                :height="500"
              ></bar-chart>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Acumulado Tractores por Año </v-card-title>
            <v-card-text>
              <bar-chart
                :data-set="BarDataTraSucursalSet"
                :height="500"
              ></bar-chart>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12">
          <v-card flat>
            <v-card-title> Ultima Venta Sucursal </v-card-title>
            <v-card-text>
              <v-simple-table class="elevation-4" max-height="500" fixed-header>
                <template #default>
                  <thead>
                    <tr>
                      <th>Cliente</th>
                      <th>Sucursal</th>
                      <th>Producto</th>
                      <th>Precio Venta</th>
                      <th>Tipo Venta</th>
                      <th>Vendedor</th>
                      <th>Direcciones</th>
                      <th>Teléfonos</th>
                      <th>Fecha Factura</th>
                      <th>Año</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(item, key) in ultimasInvoiceSucursal"
                      :key="key"
                    >
                      <td>
                        <div class="text-secondary">
                          {{ item.clave_cliente }}
                        </div>
                        {{ item.cliente }}
                      </td>
                      <td>{{ item.sucursal }}</td>
                      <td>
                        <div>{{ item.NIP }}</div>
                        {{ item.producto }}
                      </td>
                      <td>{{ item.precio_venta | currency }}</td>
                      <td class="text-no-wrap">
                        <div class="font-weight-bold">{{ item.tipo }}</div>
                        {{ item.tipo_venta || "Otro" }}
                      </td>
                      <td>
                        <div>{{ item.clave_vendedor }}</div>
                        {{ item.nombre_vendedor }}
                      </td>
                      <td>{{ item.addresses }}</td>
                      <td>{{ item.phones }}</td>
                      <td>{{ item.invoice_date }}</td>
                      <td>{{ item.year }}</td>
                    </tr>
                  </tbody>
                </template>
              </v-simple-table>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>
<script>
// import SalesCustomerBar from "./SalesCustomerBar.vue";
// import SalesCustomerPie from "./SalesCustomerPie.vue";
import PieChart from "./PieChart.vue";
import BarChart from "./BarChart.vue";

export default {
  name: "SalesCustomerHistory",
  components: {
    // SalesCustomerPie,
    // SalesCustomerBar,
    PieChart,
    BarChart,
  },
  mounted() {
    const _this = this;
    _this.$store.commit("setBreadcrumbs", [
      { label: "Historial Ventas Clientes", name: "" },
    ]);
    _this.getOptions();
  },
  data() {
    return {
      headers: [
        {
          text: "Cliente",
          align: "start",
          filterable: false,
          value: "cliente",
          sortable: false,
        },
        {
          text: "Sucursal",
          align: "start",
          filterable: false,
          value: "sucursal",
          sortable: false,
        },
        { text: "Vendedor", value: "nombre_vendedor", sortable: false },
        { text: "Venta", value: "tipo_venta", sortable: false },
        // { text: "Estado / Municipio", value: "Estado", sortable: false },
        // { text: "Producto / Tipo Venta", value: "Producto", sortable: false },
        { text: "Serie Producto", value: "NIP", width: 100, sortable: false },
        {
          text: "Fecha Factura",
          align: "end",
          value: "invoice_date",
          sortable: false,
        },
        {
          text: "Importe",
          align: "end",
          value: "precio_venta",
          sortable: false,
        },
      ],
      search: "",
      searchCustomer: "",
      items: [],
      sumTotalVentasAg: 0,
      sumTotalVentasAgMX: 0,
      ventasPorCliente: [],
      barGrafica: [],
      ultimasInvoicePorCliente: [],
      ventasPorVendedor: [],
      barVendedorGrafica: [],
      ultimasInvoiceVendedor: [],
      ventasPorSucursal: [],
      barSucursalGrafica: [],
      barTipoVentaSucursalGrafica: [],
      ultimasInvoiceSucursal: [],
      barTipoVentaVendedorGrafica: [],
      lastUpdated: "",
      totalItems: 0,
      pagination: {
        itemsPerPage: 10,
        page: 1,
      },
      filters: {
        clave_cliente: [],
        clave_vendedor: [],
        sucursales: [],
        lineas: [],
        years: [],
        currency: [],
        tipo_venta: [],
        municipio: [],
        estado: [],
      },
      options: {},
    };
  },
  computed: {
    dataLabels() {
      return this.ventasPorCliente.map(
        (item) => `${item.tipo_venta || "Otro"} (${item.tipo}) ${item.cliente}`
      );
    },
    dataValues() {
      return this.ventasPorCliente.map((item) => item.total_comprado);
    },
    dataVendedorLabels() {
      return this.ventasPorVendedor.map(
        (item) =>
          `${item.tipo_venta || "Otro"} (${item.tipo}) ${item.clave_vendedor}`
      );
    },
    dataVendedorValues() {
      return this.ventasPorVendedor.map((item) => item.total_comprado);
    },
    dataSucursalLabels() {
      return this.ventasPorSucursal.map(
        (item) => `${item.tipo_venta || "Otro"} (${item.tipo}) ${item.sucursal}`
      );
    },
    dataSucursalValues() {
      return this.ventasPorSucursal.map((item) => item.total_comprado);
    },
    BarDataSet() {
      const _this = this;
      return {
        labels: _this.barGrafica[0].years.map(String),
        datasets: _this.barGrafica.map((clienteData, index) => ({
          label: clienteData.cliente,
          data: clienteData.acumulado,
          backgroundColor: _this.getRandomColor(index),
        })),
      };
    },
    BarDataVendedorSet() {
      const _this = this;
      return {
        labels: _this.barVendedorGrafica[0].years.map(String),
        datasets: _this.barVendedorGrafica.map((clienteData, index) => ({
          label: clienteData.cliente,
          data: clienteData.acumulado,
          backgroundColor: _this.getRandomColor(index),
        })),
      };
    },
    BarDataTraVendedorSet() {
      const _this = this;
      return {
        labels: _this.barTipoVentaVendedorGrafica[0].years.map(String),
        datasets: _this.barTipoVentaVendedorGrafica.map(
          (clienteData, index) => ({
            label: clienteData.cliente,
            data: clienteData.acumulado,
            backgroundColor: _this.getRandomColor(index),
          })
        ),
      };
    },
    BarDataSucursalSet() {
      const _this = this;
      return {
        labels: _this.barSucursalGrafica[0].years.map(String),
        datasets: _this.barSucursalGrafica.map((clienteData, index) => ({
          label: clienteData.cliente,
          data: clienteData.acumulado,
          backgroundColor: _this.getRandomColor(index),
        })),
      };
    },
    BarDataTraSucursalSet() {
      const _this = this;
      return {
        labels: _this.barTipoVentaSucursalGrafica[0].years.map(String),
        datasets: _this.barTipoVentaSucursalGrafica.map(
          (clienteData, index) => ({
            label: clienteData.cliente,
            data: clienteData.acumulado,
            backgroundColor: _this.getRandomColor(index),
          })
        ),
      };
    },
    FilterClienteReduce() {
      return this.filters.clave_cliente.reduce((result, item) => {
        typeof item === "object" && item.hasOwnProperty("Clave_Cliente")
          ? result.push(item.Clave_Cliente)
          : result.push(item);
        return result;
      }, []);
    },
    FilterVendedorReduce() {
      return this.filters.clave_vendedor.reduce((result, item) => {
        typeof item === "object" && item.hasOwnProperty("ClaveVendedor")
          ? result.push(item.ClaveVendedor)
          : result.push(item);
        return result;
      }, []);
    },
    OptionsYears() {
      const _this = this;
      return _this.options.Años && _this.options.Años.length > 0
        ? _this.options.Años.sort()
        : [];
    },
  },
  watch: {
    pagination: {
      handler: _.debounce(function (newV, oldV) {
        if (newV !== oldV) {
          this.getData(() => {});
        }
      }, 999),
      deep: true,
      immediate: false,
    },
    search: {
      handler: _.debounce(function (v) {
        this.getData({ page: 1, per_page: 10 });
      }, 1200),
    },
    // searchCustomer: {
    //   handler: _.debounce(function (v) {
    //     this.getData({ page: 1, per_page: 5 });
    //   }, 1200),
    // },
  },
  methods: {
    getRandomColor(index) {
      // Función para generar colores aleatorios
      const colors = [
        "#41B883",
        "#E46651",
        "#00D8FF",
        "#DD1B16",
        "#F4FF81",
        "#FF9800",
        "#90A4AE",
        "#D84315",
        "#B9F6CA",
        "#01579B",
        // Puedes agregar más colores aquí para más clientes
      ];
      return colors[index % colors.length];
    },
    cleanFilter() {
      const _this = this;

      this.$nextTick(() => {
        _this.filters.clave_cliente = [];
        _this.filters.clave_vendedor = [];
        _this.filters.sucursales = [];
        _this.filters.lineas = [];
        _this.filters.years = [];
        _this.filters.currency = [];
        _this.filters.tipo_venta = [];
        _this.filters.municipio = [];
        _this.filters.estado = [];
        _this.pagination.page = 1;
        _this.pagination = Object.assign(
          {},
          {
            page: 1,
            itemsPerPage: 10,
          }
        );
      });
    },
    searchBtn() {
      const _this = this;
      this.$nextTick(() => {
        _this.pagination = Object.assign(
          {},
          {
            page: 1,
            itemsPerPage: 10,
          }
        );
      });
    },
    async getData({ page, per_page }) {
      const _this = this;
      let params = {
        ..._this.filters,
        clave_cliente: _this.FilterClienteReduce,
        clave_vendedor: _this.FilterVendedorReduce,
        search: _this.search,
        // searchCustomer: _this.searchCustomer,
        // order_by: _this.pagination.sortBy[0] || "id",
        // order_sort: _this.pagination.sortDesc[0] ? "asc" : "desc",
        // order_by: _this.pagination.sortBy[0] || "id",
        page: page ?? this.pagination.page,
        per_page: per_page ?? this.pagination.itemsPerPage,
      };
      const {
        data: {
          data: {
            items,
            sumatoriaTotal,
            lastUpdated,
            ventasPorCliente,
            barGrafica,
            ultimasInvoicePorCliente,
            ventasPorVendedor,
            barVendedorGrafica,
            ultimasInvoiceVendedor,
            ventasPorSucursal,
            barSucursalGrafica,
            barTipoVentaSucursalGrafica,
            ultimasInvoiceSucursal,
            barTipoVentaVendedorGrafica,
          },
          message,
        },
      } = await axios.get("/admin/marketing/sales-customer", { params });
      this.$nextTick(() => {
        _this.items = items.data;
        _this.totalItems = items.total;
      });

      this.sumTotalVentasAg = sumatoriaTotal;
      this.ventasPorCliente = ventasPorCliente;
      this.barGrafica = barGrafica;
      this.lastUpdated = lastUpdated;
      this.ultimasInvoicePorCliente = ultimasInvoicePorCliente;
      this.ventasPorVendedor = ventasPorVendedor;
      this.barVendedorGrafica = barVendedorGrafica;
      this.ultimasInvoiceVendedor = ultimasInvoiceVendedor;
      this.ventasPorSucursal = ventasPorSucursal;
      this.barSucursalGrafica = barSucursalGrafica;
      this.ultimasInvoiceSucursal = ultimasInvoiceSucursal;
      this.barTipoVentaVendedorGrafica = barTipoVentaVendedorGrafica;
      this.barTipoVentaSucursalGrafica = barTipoVentaSucursalGrafica;

      this.$store.commit("showSnackbar", {
        message: message,
        color: "success",
        duration: 3000,
      });
    },
    async getOptions() {
      const {
        data: {
          data: { options },
        },
      } = await axios.get("/admin/marketing/sales-customer/filters");

      this.options = options;
    },
    customFilterCliente(item, queryText, itemText) {
      const words = queryText.toLowerCase().split(" ");

      return words.every((word) => {
        // console.log(item.Cliente.toLowerCase());
        const nameMatch = item.Cliente.toLowerCase().includes(word);
        const claveMatch = item.Clave_Cliente.toLowerCase().includes(word);

        return nameMatch || claveMatch;
      });
    },
    customFilterVendedor(item, queryText, itemText) {
      const words = queryText.toLowerCase().split(" ");

      return words.every((word) => {
        const nameMatch = item.Nombre.toLowerCase().includes(word);
        const claveMatch = item.ClaveVendedor.toLowerCase().includes(word);

        return nameMatch || claveMatch;
      });
    },
    toggle() {
      this.$nextTick(() => {
        if (this.likesAllFruit) {
          this.selectedFruits = [];
        } else {
          this.selectedFruits = this.fruits.slice();
        }
      });
    },

    exportToCsv(fileName, data) {
      // Crear un libro de trabajo
      var workbook = XLSX.utils.book_new();
      var sheetName = "Datos"; // Nombre de la hoja

      // Convertir los datos a una hoja de trabajo
      var worksheet = XLSX.utils.json_to_sheet(data);

      // Agregar la hoja de trabajo al libro de trabajo
      XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);

      // Generar un archivo Excel (xlsx)
      var excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });

      // Convertir el buffer a un blob
      var blob = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });

      // Descargar el archivo Excel
      // var fileName = "datos.xlsx"; // Nombre del archivo
      saveAs(blob, fileName);
    },
  },
};
</script>
<style >
</style>