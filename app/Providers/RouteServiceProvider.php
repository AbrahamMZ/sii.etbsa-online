<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    protected $namespaceAdmin = 'App\Http\Controllers\Admin';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        // Route::middleware('web')
        //      ->namespace($this->namespace)
        //      ->group(base_path('routes/web.php'));
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(function () {
                require base_path('routes/web.php');
                require base_path('routes/web/gps.php');
                require base_path('routes/web/vehicle.php');
                require base_path('routes/web/resources_share.php');
                require base_path('routes/web/purchases.php');
                require base_path('routes/web/rrhh.php');
                require base_path('routes/web/customers.php');
                require base_path('routes/web/media.php');
                require base_path('routes/web/tracking.php');
                require base_path('routes/web/nt.php');
                require base_path('routes/web/product.php');
                require base_path('routes/web/cargosInternos.php');
                require base_path('routes/web/sellers.php');
                require base_path('routes/web/common.php');
            });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
