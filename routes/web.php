<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

use Barryvdh\Reflection\DocBlock\Tag\ReturnTag;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

Route::get('/', 'Front\\HomeController@index')->name('front.home');
Route::get('files/{id}/preview', 'Front\\FileController@filePreview')->name('front.file.preview');
Route::get('files/{id}/download', 'Front\\FileController@fileDownload')->name('front.file.download');

Route::get('/proveedores', 'Supplier\\HomeController@index')->name('proveedores.home');

Auth::routes();

// NOTE:
// remove the demo middleware before you start on a project, this middleware if only
// for demo purpose to prevent viewers to modify data on a live demo site

// admin
Route::prefix('admin')->namespace('Admin')->middleware(['auth'])->group(function () {
    // single page
    Route::get('/', 'SinglePageController@displaySPA')->name('admin.spa');

    // resource routes
    Route::resource('users', 'UserController');
    Route::resource('sellers', 'SellerController');

    Route::resource('groups', 'GroupController');
    Route::resource('permissions', 'PermissionController');
    Route::resource('files', 'FileController');
    Route::resource('file-groups', 'FileGroupController');

    Route::resource('prospects', 'ProspectController');

    //Notification
    Route::get('notification', 'NotificationController@index')->name('notification.index');
    Route::delete('notification/{notification}', 'NotificationController@destroy')->name('notification.destroy');

    //Marketing
    Route::get('marketing/sales_history', 'MarketingController@salesHistory')->name('salesHistory');
    Route::get('marketing/sales_history/resources', 'MarketingController@resources')->name('salesHistoryResources');
    Route::get('marketing/export', 'ExportController@exportMarketing')->name('marketingExport');

    // ResourcesController
    Route::get('resource/agencies', 'ResourcesShareController@getAgencies')->name('resources.agencies');
    Route::get('resource/users', 'ResourcesShareController@getUser')->name('resources.users');
    Route::get('resource/options', 'ResourcesShareController@getOptions')->name('resources.options');

    // Resources Grafics Metas
    Route::get('metas_sucursal', function () {
        $metas = DB::table('metas_sucursales')->orderBy('sucursal')->get();

        $unicos = $metas->map(function ($item) {
            return $item->sucursal;
        })->unique();

        foreach ($unicos as $key => $value) {
            $meta = $metas->filter(function ($item) use ($value) {
                return $item->sucursal == $value;
            })->map(function ($item, $key) {
                if ($item->meta_mes_actual > 0) {
                    $venta_meta_mes = ($item->venta_mes_actual * 100) /  $item->meta_mes_actual;
                }
                // if ($item->meta_anual > 0) {
                //     $venta_meta_anual = ($item->venta_enero_mes_actual * 100) /  $item->meta_anual;
                // }
                // if ($item->meta_enero_mes_actual > 0) {
                //     $venta_meta_mes_actual = ($item->venta_enero_mes_actual * 100) /  $item->meta_enero_mes_actual;
                // }
                return [
                    'departamento' => $item->departamento,
                    'meta_mes_actual' => $item->meta_mes_actual,
                    'venta_mes_actual' => ceil($venta_meta_mes ?? 0),
                    'venta_mes' => $item->venta_mes_actual ?? 0,
                ];
            });
            // sucursal,departamento,clave_vendedor,nombre_vendedor,venta_total,margen_total,gasto

            $dpto = $meta->pluck('departamento');
            $metaActual = $meta->pluck('meta_mes_actual');
            $ventaActual = $meta->pluck('venta_mes_actual');
            $ventaMes = $meta->pluck('venta_mes');
            $dataset[] = [
                'name' => $value,
                'title' => ['text' => "Meta $ " . ceil($metaActual[0])],
                'tooltip' => [
                    'trigger' => "axis",
                    'axisPointer' => [
                        'type' => "shadow",
                    ],
                    'formatter' => '{b}: $ ' . $ventaMes[0]
                ],
                'yAxis' => [
                    'type' => 'category',
                    'data' =>  $dpto->all()
                ],
                'series' => [
                    // [
                    //     'name' => 'meta',
                    //     'type' => 'bar',
                    //     'label' => [
                    //         'show' => true,
                    //         'position' => 'right'
                    //     ],
                    //     'data' => $metaActual->all()
                    // ],
                    [
                        'name' => 'venta',
                        'type' => 'bar',
                        'label' => [
                            'show' => true,
                            'position' => 'inside',
                            'fontWeight' => 'bold',
                            'fontSize' => 32,
                            'formatter' => '{c}%'
                        ],
                        'data' => $ventaActual->all()
                    ],
                ],
            ];
        };
        return Response($dataset);
    });

    Route::get('metas_sucursal/{sucursal}', function ($sucursal) {
        $metas = DB::table('metas_sucursales')->where('sucursal', $sucursal)->get();
        // "sucursal": "Celaya",
        // "departamento": "Ventas Agricola",
        // "venta_mes_actual": 22137826.4,
        // "venta_mes_año_pasado": 16898860.48,
        // "meta_mes_actual": 17050000,
        // "meta_anual": 155000000,
        // "meta_enero_mes_actual": 137950000,
        // "venta_enero_mes_actual": 153883924.3,
        // "meta_enero_mes_actual_año_pasado": 161316395.1

        $data = $metas->map(function ($item, $key) {
            if ($item->meta_mes_actual > 0) {
                $venta_meta_mes = ($item->venta_mes_actual * 100) /  $item->meta_mes_actual;
            }
            if ($item->meta_anual > 0) {
                $venta_meta_anual = ($item->venta_enero_mes_actual * 100) /  $item->meta_anual;
            }
            if ($item->meta_enero_mes_actual > 0) {
                $venta_meta_mes_actual = ($item->venta_enero_mes_actual * 100) /  $item->meta_enero_mes_actual;
            }
            return [
                'Sucursal' => $item->sucursal,
                'Departamento' => $item->departamento,
                'Meta Mes' => $item->meta_mes_actual,
                'Venta' => $item->venta_mes_actual,
                'Venta vs Meta Mes' => $venta_meta_mes ?? 0,
                'Venta vs Meta Anual' => $venta_meta_anual ?? 0,
                'Venta vs Meta Ene Mes Actual' => $venta_meta_mes_actual ?? 0
            ];
        });

        return Response($data);
    });

    Route::get('ventas_vendedores', function () {
        $vendedores = DB::table('chart_ventas_vendedores')->get();

        foreach ($vendedores as $key => $value) {
            $venta = $value->venta_total;
            $margen = $value->margen_total;
            $gasto = $value->gasto;

            $dataset[] = [
                'sucursal' => $value->sucursal,
                'depatamento' => $value->departamento,
                'vendedor' => $value->nombre_vendedor,
                'title' => [
                    'text' => "Venta Total $ " . $venta,
                ],
                'series' => [
                    'type' => 'bar',

                    'data' => [
                        $gasto,
                        [
                            'value' => $margen,
                            'itemStyle' => [
                                'color' => '#40C4FF'
                            ]
                        ],
                        [
                            'value' => $venta,
                            'itemStyle' => [
                                'color' => '#66BB6A',

                            ]
                        ]
                    ],
                    'coordinateSystem' => 'polar',
                    // 'roundCap' => true,
                    'itemStyle' => [
                        'borderColor' => 'green',
                        'opacity' => 0.8,
                        'borderWidth' => 1.5
                    ],
                    'label' => [
                        'show' => false,
                        'position' => 'middle',
                    ],

                ]
            ];
        }
        return Response($dataset);
    });
});

//Clear Cache facade value:sucursal
Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function () {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function () {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function () {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function () {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});
