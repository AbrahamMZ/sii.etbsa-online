export const flujo_efectivo = [
  {
    path: "/flujo",
    component: require("@admin/FlujoEfectivo/Index").default,
    children: [
      {
        path: "ingresos",
        name: "poliza.ingresos.list",
        props: () => ({ propTipoPoliza: "ingresos" }),
        component: require("@admin/FlujoEfectivo/PolizaIngresos/Index").default,
      },
      {
        path: "create",
        name: "poliza.ingresos.create",
        props: () => ({ propTipoPoliza: "ingresos", unidentified: false }),
        component: require("@admin/FlujoEfectivo/PolizaIngresos/Create")
          .default,
      },
      {
        path: "no-identificados",
        name: "poliza.ingresos.unidentified",
        props: () => ({
          propTipoPoliza: "ingresos",
          unidentified: true,
        }),
        component: require("@admin/FlujoEfectivo/PolizaIngresos/Index").default,
      },
      {
        path: "egresos",
        name: "poliza.egresos.list",
        props: () => ({ propTipoPoliza: "egresos" }),
        component: require("@admin/FlujoEfectivo/PolizaEgresos/Index").default,
      },
      {
        path: "transferencias",
        name: "poliza.tranferencias.list",
        props: () => ({ propTipoPoliza: "Transferencias" }),
        component: require("@admin/FlujoEfectivo/PolizaTransferencias/Index")
          .default,
      },
    ],
  },
  {
    path: "/cuentas-sucursal",
    name: "cuentas.agencias",
    component: require("@admin/FlujoEfectivo/agencyBankAccounts/Index").default,
  },
];
