<?php

namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\Currency;
use App\Components\Common\Models\Estatus;
use App\Components\Core\Utilities\Helpers;
use App\Components\EquipDB\Models\CatClientes;
use App\Components\EquipDB\Models\FacturacionAG;
use App\Components\Product\Models\ProductCategory;
use App\Components\Tracking\Models\Prospect;
use App\Components\Tracking\Models\TrackingProspect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SalesCustomerHistoryController extends AdminController
{

    public function __construct()
    {
    }

    public function index(Request $request)
    {

        // dd($request->all(), $request['clave_cliente'], $request->clave_cliente);
        $filters = $request->all();

        $query = FacturacionAG::from('facturacion_utf8 AS fa')->select(
            'CVE_CLIENTE as clave_cliente',
            'fa.SUCURSAL AS sucursal',
            'fa.LINEA AS linea',
            'CIUDAD / MUNICIPIO AS municipio',
            'fa.CP',
            'NIP',
            'DESCRIPCION PRODUCTO AS producto',
            'NOMBRE CLIENTE AS cliente',
            'PRECIO VENTA AS precio_venta',
            'FECHA FACTURA AS invoice_date',
            'ctv.clave AS tipo_venta',
            'ctv.descripcion AS tipo_venta_nombre',
            // 'cv.clave_vendodor AS clave_vendedor',
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.clave_vendodor <> '' THEN cv.clave_vendodor
                ELSE fa.CVE_VENDEDOR
            END , 'N/E')
            AS clave_vendedor"
            ),
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.nombre_vendor <> '' THEN cv.nombre_vendor
                ELSE fa.`NOMBRE VENDEDOR`
            END , 'N/E')
            AS nombre_vendedor"
            ),
            // 'cv.nombre_vendor AS nombre_vendedor',
            DB::raw("fa.CALLE ||','|| fa.`COLONIA /COMUNIDAD`||','|| fa.CP ||','||fa.`CIUDAD / MUNICIPIO`||','|| fa.ESTADO ||','|| fa.PAIS AS addresses"),
            DB::raw("fa.`Telefono 1` ||','|| fa.`Telefono 2`||','|| fa.`Telefono 3` AS phones"),
            DB::raw('SUBSTR(`FECHA FACTURA`, -4) as year')
        )
            ->leftJoin('vendedores_utf8 AS cv', 'cv.clave_vendodor', '=', 'fa.CVE_VENDEDOR')
            ->leftJoin('catTipoVenta AS ctv', 'ctv.clave', '=', 'fa.TIPO DE VENTA')
            ->orderByRaw("SUBSTR(`FECHA FACTURA`, -4) DESC");
        // ->orderBy('invoice_date', "desc");
        // ->orderBy('invoice_date');
        // ->orderByRaw("strftime('%Y', fa.`FECHA FACTURA`)");


        $query->when($filters['clave_cliente'] ?? null, function ($query, $clave_cliente) {
            $query->where(function ($q) use ($clave_cliente) {
                if (is_array($clave_cliente)) {
                    foreach ($clave_cliente as $term) {
                        $q->orWhere('fa.CVE_CLIENTE', 'LIKE', "%{$term}%");
                        $q->orWhere('fa.NOMBRE CLIENTE', 'LIKE', "%{$term}%");
                    }
                }
            });
        })->when($filters['clave_vendedor'] ?? null, function ($query, $clave_vendedor) {
            $query->where(function ($q) use ($clave_vendedor) {
                if (is_array($clave_vendedor)) {
                    foreach ($clave_vendedor as $term) {
                        $q->orWhere('cv.clave_vendodor', 'LIKE', "%{$term}%");
                        $q->orWhere('cv.nombre_vendor', 'LIKE', "%{$term}%");

                    }
                }
            });
        })->when($filters['sucursales'] ?? null, function ($query, $sucursales) {
            // $query->whereIn('fa.SUCURSAL', $sucursales);
            $query->where(function ($q) use ($sucursales) {
                if (is_array($sucursales)) {
                    foreach ($sucursales as $term) {
                        $q->orWhere('fa.SUCURSAL', 'LIKE', "%{$term}%");

                    }
                }
            });
        })->when($filters['years'] ?? null, function ($query, $years) {
            $query->whereIn(DB::raw('SUBSTR(`FECHA FACTURA`, -4)'), $years);
        });

        $query->when($request->search ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('fa.DESCRIPCION PRODUCTO', 'like', "%{$search}%")
                    ->orWhere('fa.NIP', 'like', "%{$search}%")
                    ->orWhere('cv.MODELO', 'LIKE', "%{$search}%");
            });
        });


        // Permiso de Sucursal
        $query->when(
            auth()->user()->inGroup('Gerente') ?? null,
            function ($query) {
                $query->whereIn('ID_SUC', auth()->user()->seller_agency->pluck('code'));
            }
        );



        if ($request['per_page'] == -1) {
            $request['per_page'] = 999999;
        }

        $sumatoriaTotal = $query->sum('PRECIO VENTA');
        if ($request->has('clave_cliente') || $request->has('clave_vendedor') || $request->has('sucursales')) {
            $results = $query->orderByRaw("SUBSTR(`FECHA FACTURA`, -4) DESC")->get();
            $allYears = $results->pluck('year')->unique()->sort()->values();
        }


        // $ventasPorMunicipio = [];

        $query->chunk(2000, function ($invoices) {
            return $invoices;
        });
        $items = $query->paginate($request['per_page'] ?? 10);

        $ventasPorCliente = [];
        $barGrafica = [];
        $ultimasInvoicePorCliente = [];
        if ($request->has('clave_cliente')) {

            // Grafica de Pie y Tabla 
            $ventasPorCliente = $results->groupBy(function ($item) {
                return $item->tipo_venta . '_' . $item->clave_cliente;
                // return $item->tipo_venta . '_' . $item->cliente;
                // return $item->tipo_venta;
            })->map(function ($groupedItems) {
                return [
                    'clave_cliente' => $groupedItems[0]->clave_cliente,
                    'cliente' => $groupedItems[0]->cliente,
                    'tipo' => $groupedItems[0]->tipo_venta,
                    'tipo_venta' => $groupedItems[0]->tipo_venta_nombre,
                    'total_comprado' => $groupedItems->sum('precio_venta'),
                    'count' => $groupedItems->count()
                ];
            })->values();

            // Grafica de Barras
            // $allYears = $results->pluck('year')->unique()->sort()->values();
            $barGrafica = $results->groupBy('clave_cliente')->map(function ($ventasPorCliente, $cliente) use ($allYears) {
                $acumuladoPorYear = $ventasPorCliente->groupBy('year')->map(function ($ventasPorYear) {
                    return $ventasPorYear->sum('precio_venta');
                });

                // Rellenar con 0 los años sin ventas
                $acumuladoPorYear = $acumuladoPorYear->union($allYears->flip()->map(function () {
                    return 0;
                }))->sortKeys()->values();


                return [
                    'cliente' => $cliente,
                    'years' => $allYears,
                    'acumulado' => $acumuladoPorYear,
                ];
            })->values();

            // Tabla Ultima Venta
            $ultimasInvoicePorCliente = $results->mapToGroups(function ($item) {
                $key = $item->clave_cliente . '-' . $item->year;
                return [$key => $item];
            })->map(function ($invoiceDates) use ($filters) {
                return $filters['years'] ?? false ? $invoiceDates->last() : $invoiceDates->first();
            });
        }

        $ventasPorVendedor = [];
        $barVendedorGrafica = [];
        $barTipoVentaVendedorGrafica = [];
        $ultimasInvoiceVendedor = [];
        if ($request->has('clave_vendedor')) {
            $ventasPorVendedor = $results->groupBy(function ($item) {
                return $item->tipo_venta . '_' . $item->clave_vendedor;
                // return $item->clave_vendedor;
                // return $item->tipo_venta . '_' . $item->cliente;
                // return $item->tipo_venta;
            })->map(function ($groupedItems) {
                return [
                    'clave_vendedor' => $groupedItems[0]->clave_vendedor,
                    'vendedor' => $groupedItems[0]->nombre_vendedor,
                    'tipo' => $groupedItems[0]->tipo_venta,
                    'tipo_venta' => $groupedItems[0]->tipo_venta_nombre,
                    'total_comprado' => $groupedItems->sum('precio_venta'),
                    'count' => $groupedItems->count()
                ];
            })->values();

            // $allYears = $results->pluck('year')->unique()->sort()->values();
            $barVendedorGrafica = $results->groupBy('clave_vendedor')->map(function ($ventasPorCliente, $cliente) use ($allYears) {
                $acumuladoPorYear = $ventasPorCliente->groupBy('year')->map(function ($ventasPorYear) {
                    return $ventasPorYear->sum('precio_venta');
                });


                // Rellenar con 0 los años sin ventas
                $acumuladoPorYear = $acumuladoPorYear->union($allYears->flip()->map(function () {
                    return 0;
                }))->sortKeys()->values();


                return [
                    'cliente' => $cliente,
                    'years' => $allYears,
                    'acumulado' => $acumuladoPorYear,
                ];
            })->values();

            $barTipoVentaVendedorGrafica = $results->groupBy('clave_vendedor')->map(function ($ventasPorCliente, $cliente) use ($allYears) {
                $acumuladoPorTipoVenta = $ventasPorCliente->groupBy('year')->map(function ($VentasYearTraVendedor) {

                    return $VentasYearTraVendedor->filter(function ($value, $key) {
                        return $value->tipo_venta == "TRA";
                    })
                        ->count();
                });

                // Rellenar con 0 los años sin ventas
                $acumuladoPorTipoVenta = $acumuladoPorTipoVenta->union($allYears->flip()->map(function () {
                    return 0;
                }))->sortKeys()->values();


                return [
                    'cliente' => $cliente,
                    'years' => $allYears,
                    'acumulado' => $acumuladoPorTipoVenta,
                ];
            })->values();


            $ultimasInvoiceVendedor = $results->mapToGroups(function ($item) {
                return [$item->clave_vendedor . '-' . $item->year => $item];
            })->map(function ($invoiceDates) {
                return $invoiceDates->last();
            });
        }
        $ventasPorSucursal = [];
        $barSucursalGrafica = [];
        $barTipoVentaSucursalGrafica = [];
        $ultimasInvoiceSucursal = [];
        if ($request->has('sucursales')) {
            $ventasPorSucursal = $results->groupBy(function ($item) {
                return $item->tipo_venta . '_' . $item->sucursal;

            })->map(function ($groupedItems) {
                return [
                    'sucursal' => $groupedItems[0]->sucursal,
                    'tipo' => $groupedItems[0]->tipo_venta,
                    'tipo_venta' => $groupedItems[0]->tipo_venta_nombre,
                    'total_comprado' => $groupedItems->sum('precio_venta'),
                    'count' => $groupedItems->count()
                ];
            })->values();


            $barSucursalGrafica = $results->groupBy('sucursal')->map(function ($ventasPorCliente, $cliente) use ($allYears) {
                $acumuladoPorYear = $ventasPorCliente->groupBy('year')->map(function ($ventasPorYear) {
                    return $ventasPorYear->sum('precio_venta');
                });

                // Rellenar con 0 los años sin ventas
                $acumuladoPorYear = $acumuladoPorYear->union($allYears->flip()->map(function () {
                    return 0;
                }))->sortKeys()->values();


                return [
                    'cliente' => $cliente,
                    'years' => $allYears,
                    'acumulado' => $acumuladoPorYear,
                ];
            })->values();

            $barTipoVentaSucursalGrafica = $results->groupBy('sucursal')->map(function ($ventasPorCliente, $cliente) use ($allYears) {
                $acumuladoPorTipoVenta = $ventasPorCliente->groupBy('year')->map(function ($VentasYearTraVendedor) {

                    return $VentasYearTraVendedor->filter(function ($value, $key) {
                        return $value->tipo_venta == "TRA";
                    })
                        ->count();
                });

                // Rellenar con 0 los años sin ventas
                $acumuladoPorTipoVenta = $acumuladoPorTipoVenta->union($allYears->flip()->map(function () {
                    return 0;
                }))->sortKeys()->values();


                return [
                    'cliente' => $cliente,
                    'years' => $allYears,
                    'acumulado' => $acumuladoPorTipoVenta,
                ];
            })->values();

            $ultimasInvoiceSucursal = $results->mapToGroups(function ($item) {
                return [$item->sucursal => $item];
            })->map(function ($invoiceDates) {
                return $invoiceDates->last();
            });
        }

        // Municpios Default Chart
        $archivo = database_path('EquipDB.db3');
        $fechaModificacion = File::lastModified($archivo);
        $lastUpdated = date("Y-m-d H:i:s", $fechaModificacion);

        return $this->sendResponseOk(
            compact(
                'lastUpdated',
                'items',
                'sumatoriaTotal',
                'ventasPorCliente',
                'barGrafica',
                'ultimasInvoicePorCliente',
                'ventasPorVendedor',
                'barVendedorGrafica',
                'barTipoVentaVendedorGrafica',
                'ultimasInvoiceVendedor',
                'ventasPorSucursal',
                'barSucursalGrafica',
                'barTipoVentaSucursalGrafica',
                'ultimasInvoiceSucursal',
                // 'ventasPorMunicipio'
            )
        );
    }

    public function getOptions()
    {
        $options = [
            'Clientes' => FacturacionAG::distinct()
                ->select(
                    'CVE_CLIENTE AS Clave_Cliente',
                    'NOMBRE CLIENTE AS Cliente'
                )
                // ->leftJoin('catClientes AS cc', 'cc.CLAVE CLIENTE', '=', 'invoice_jd.CLAVE PROVEEDOR')
                ->get(),
            'Vendedores' => FacturacionAG::distinct()
                ->select(
                    'cv.clave_vendodor AS ClaveVendedor',
                    'cv.nombre_vendor AS Nombre'
                )
                ->leftJoin('vendedores_utf8 AS cv', 'cv.clave_vendodor', '=', 'facturacion_utf8.CVE_VENDEDOR')
                ->whereNotNull('cv.clave_vendodor')
                ->get(),
            // 'Vendedores' => DB::connection('sqlite_equip_db')->table('catVendedores as cv')->distinct()
            //     ->select('cv.Clave vendedor AS ClaveVendedor', 'cv.Nombre Vendedor AS Nombre')
            //     ->get(),
            'Sucursales' => DB::connection('sqlite_equip_db')->table('facturacion_utf8')->distinct()
                ->select('SUCURSAL as name')
                ->pluck('name'),
            // DB::connection('sqlite_equip_db')->table('catSucursales as cs')->distinct()
            //     ->get(),
            'Linea' => DB::connection('sqlite_equip_db')->table('catSucursales as cs')->distinct()
                ->select('cs.linea')
                ->get(),
            // 'SerieFiscal' => DB::connection('sqlite_equip_db')->table('invoice_jd')->distinct()
            //     ->select('SERIE FISCAL AS serie_fiscal')->whereNotNull('SERIE FISCAL')
            //     ->pluck('serie_fiscal'),
            // 'Moneda' => DB::connection('sqlite_equip_db')->table('invoice_jd')->distinct()
            //     ->select('MONEDA')->whereNotNull('MONEDA')
            //     ->get(),
            'TipoVenta' => DB::connection('sqlite_equip_db')->table('invoice_jd')->distinct()
                ->select('ctv.clave', 'ctv.descripcion')
                ->join('catTipoVenta as ctv', 'invoice_jd.TIPO DE VENTA', '=', 'ctv.clave')
                ->whereNotNull('invoice_jd.TIPO DE VENTA')
                ->get(),
            'Años' => DB::connection('sqlite_equip_db')->table('facturacion_utf8')->distinct()
                ->select(DB::raw('SUBSTR(`FECHA FACTURA`, -4) as year'))
                ->pluck('year'),
            // 'Municipio' => DB::connection('sqlite_equip_db')->table('invoice_jd')->distinct()
            //     ->select('CIUDAD as name')
            //     ->pluck('name'),
            // 'Estado' => DB::connection('sqlite_equip_db')->table('invoice_jd')->distinct()
            //     ->select('ESTADO as name')
            //     ->pluck('name'),
        ];
        return $this->sendResponseOk(compact('options'));
    }


    public function CustomerLatestInvoices(Request $request)
    {

        $filters = $request->all();
        $query = FacturacionAG::from('facturacion_utf8 AS fa')->select(
            'CVE_CLIENTE as clave_cliente',
            'RFC CLIENTE as rfc_cliente',
            'NOMBRE CLIENTE AS cliente',
            'fa.SUCURSAL AS sucursal',
            'fa.LINEA AS linea',
            'CIUDAD / MUNICIPIO AS municipio',
            'fa.CP',
            'NIP',
            'DESCRIPCION PRODUCTO AS producto',
            'PRECIO VENTA AS precio_venta',
            'FECHA FACTURA AS invoice_date',
            'ctv.clave AS tipo_venta',
            'ctv.descripcion AS tipo_venta_nombre',
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.clave_vendodor <> '' THEN cv.clave_vendodor
                ELSE fa.CVE_VENDEDOR
            END , 'N/E')
            AS clave_vendedor"
            ),
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.nombre_vendor <> '' THEN cv.nombre_vendor
                ELSE fa.`NOMBRE VENDEDOR`
            END , 'N/E')
            AS nombre_vendedor"
            ),
            DB::raw("fa.CALLE ||','|| fa.`COLONIA /COMUNIDAD`||','|| fa.CP ||','||fa.`CIUDAD / MUNICIPIO`||','|| fa.ESTADO ||','|| fa.PAIS AS addresses"),
            DB::raw("fa.`Telefono 1` ||','|| fa.`Telefono 2`||','|| fa.`Telefono 3` AS phones"),
            DB::raw('SUBSTR(`FECHA FACTURA`, -4) as year')
        )
            ->leftJoin('vendedores_utf8 AS cv', 'cv.clave_vendodor', '=', 'fa.CVE_VENDEDOR')
            ->leftJoin('catTipoVenta AS ctv', 'ctv.clave', '=', 'fa.TIPO DE VENTA')
            ->orderByRaw("SUBSTR(`FECHA FACTURA`, -4) DESC")
            ->groupBy('cliente')
            ->latest('invoice_date');
        // ->paginate($request['per_page'] ?? 10);
        // ->groupBy('cliente')->latest('invoice_date')->get()->map(function ($groupedItems) {

        $query->when($filters['clave_cliente'] ?? null, function ($query, $clave_cliente) {
            $query->where(function ($q) use ($clave_cliente) {
                if (is_array($clave_cliente)) {
                    foreach ($clave_cliente as $term) {
                        $q->orWhere('fa.CVE_CLIENTE', 'LIKE', "{$term}");
                        $q->orWhere('fa.NOMBRE CLIENTE', 'LIKE', "%{$term}%");
                    }
                }
            });
        })->when($filters['clave_vendedor'] ?? null, function ($query, $clave_vendedor) {
            $query->where(function ($q) use ($clave_vendedor) {
                if (is_array($clave_vendedor)) {
                    foreach ($clave_vendedor as $term) {
                        $q->orWhere('cv.clave_vendodor', 'LIKE', "%{$term}%");
                        $q->orWhere('cv.nombre_vendor', 'LIKE', "%{$term}%");

                    }
                }
            });
        })->when($filters['sucursales'] ?? null, function ($query, $sucursales) {
            // $query->whereIn('fa.SUCURSAL', $sucursales);
            $query->where(function ($q) use ($sucursales) {
                if (is_array($sucursales)) {
                    foreach ($sucursales as $term) {
                        $q->orWhere('fa.SUCURSAL', 'LIKE', "%{$term}%");

                    }
                }
            });
        })->when($filters['years'] ?? null, function ($query, $years) {
            $query->whereIn(DB::raw('SUBSTR(`FECHA FACTURA`, -4)'), $years);
        });

        // permisos de sucursal
        $query->when(
            auth()->user()->inGroup('Gerente') ?? null,
            function ($query) {
                $query->whereIn('ID_SUC', auth()->user()->seller_agency->pluck('code'));
            }
        );



        if ($request['per_page'] == -1) {
            $request['per_page'] = 999999;
        }
        // $ventasPorMunicipio = [];

        $query->chunk(2000, function ($invoices) {
            return $invoices;
        });
        $items = $query->paginate($request['per_page'] ?? 10);

        return $this->sendResponseOk(
            compact(
                'items',
            )
        );

    }

    public function TrackingsByCliente(Request $request)
    {

        $filters = $request->all();
        // "clave_cliente": "LAPUERTADELOS62",
        // "rfc_cliente": "PFO170403LDA",
        // "nombre": " LA PUERTA DE LOS FRUTOS ORGANICOS",     

        $items = TrackingProspect::with('attended', 'estatus')->whereHas('prospect', function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $term = $filters['nombre'];
                $q->orWhere('full_name', 'like', "%$term%")
                    ->orWhere('company', 'like', "%$term%");
            });

        })->get();

        return $this->sendResponseOk(
            compact(
                'items',
            )
        );
    }
    public function InvoicesByCliente(Request $request)
    {

        $filters = $request->all();
        $items = FacturacionAG::from('facturacion_utf8 AS fa')->select(
            'CVE_CLIENTE as clave_cliente',
            'RFC CLIENTE as rfc_cliente',
            'NOMBRE CLIENTE AS cliente',
            'fa.SUCURSAL AS sucursal',
            'fa.LINEA AS linea',
            'CIUDAD / MUNICIPIO AS municipio',
            'fa.CP',
            'NIP',
            'DESCRIPCION PRODUCTO AS producto',
            'PRECIO VENTA AS precio_venta',
            'FECHA FACTURA AS invoice_date',
            'ctv.clave AS tipo_venta',
            'ctv.descripcion AS tipo_venta_nombre',
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.clave_vendodor <> '' THEN cv.clave_vendodor
                ELSE fa.CVE_VENDEDOR
            END , 'N/E')
            AS clave_vendedor"
            ),
            DB::raw("
            COALESCE(
            CASE
                WHEN cv.nombre_vendor <> '' THEN cv.nombre_vendor
                ELSE fa.`NOMBRE VENDEDOR`
            END , 'N/E')
            AS nombre_vendedor"
            ),
            DB::raw("fa.CALLE ||','|| fa.`COLONIA /COMUNIDAD`||','|| fa.CP ||','||fa.`CIUDAD / MUNICIPIO`||','|| fa.ESTADO ||','|| fa.PAIS AS addresses"),
            DB::raw("fa.`Telefono 1` ||','|| fa.`Telefono 2`||','|| fa.`Telefono 3` AS phones"),
            DB::raw('SUBSTR(`FECHA FACTURA`, -4) as year')
        )
            ->leftJoin('vendedores_utf8 AS cv', 'cv.clave_vendodor', '=', 'fa.CVE_VENDEDOR')
            ->leftJoin('catTipoVenta AS ctv', 'ctv.clave', '=', 'fa.TIPO DE VENTA')
            ->orderByRaw("SUBSTR(`FECHA FACTURA`, -4) DESC")
            ->where(function ($q) use ($filters) {

                // $q->orWhere('fa.CVE_CLIENTE', 'LIKE', "{$filters['clave_cliente']}");
                $q->orWhere('fa.NOMBRE CLIENTE', 'LIKE', "%{$filters['nombre']}%");

            })->get();

        return $this->sendResponseOk(
            compact(
                'items',
            )
        );

    }
    public function CreateTrackingsByCliente(Request $request)
    {
        $validate = validator($request->all(), [
            'agency_id' => 'required',
            'department_id' => 'required',
            'attended_by' => 'required',
        ]);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        return DB::transaction(function () use ($request) {
            $request['registered_by'] = Auth::user()->id;
            $request['assigned_by'] = Auth::user()->id;
            $request['currency_id'] = 1;
            $request['description_topic'] = "Contactar para Obtener Informacion, si desea algun Producto";
            $request['title'] = ProductCategory::where('name', 'FUTURA OPORTUNIDAD')->first()->name;
            $request['reference'] = "Asignado por Sistema";
            $request['assertiveness'] = 0.01;
            $request['date_next_tracking'] = Carbon::now()->addDays(15);
            $currency_name = Currency::where('id', $request['currency_id'])->first();
            $prospect = Prospect::firstOrCreate(
                ['full_name' => $request['cliente']],
                [
                    'company' => $request['cliente'],
                    'town' => $request['addresses'],
                    'phone' => $request['phones'],
                    'rfc' => $request['rfc_cliente'],
                    'registered_by' => Auth::user()->id,
                ]
            );

            $tracking = $prospect->tracking()->create($request->all());

            if (!$tracking) {
                return $this->sendResponseBadRequest("Failed create.");
            }

            $estatus = Estatus::where('key', Estatus::ESTATUS_ACTIVO)->first();
            $tracking->estatus()->associate($estatus);
            $tracking->save();
            $tracking->refresh();

            $tracking->historical()->create([
                'message' => 'Llamar para dar Seguimiento',
                'last_price' => $request['price'],
                'last_currency' => $currency_name->name,
                'type_contacted' => 'Llamada',
                'user_id' => $request['attended_by'],
                'date_next_tracking' => $request['date_next_tracking'],
                'last_assertiveness' => $request['assertiveness'],
            ]);

            return $this->sendResponseCreated(compact('tracking'), 'Se Registro Nuevo Seguimiento');
        });




    }

}