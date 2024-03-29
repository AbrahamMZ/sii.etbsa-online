<?php

namespace App\Providers;

use App\Components\CargosInternos\Models\CargosInternos;
use App\Components\Purchase\Models\PurchaseOrder;
use App\Observers\CargosInternosObserver;
use App\Observers\PurchaseOrderObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        CargosInternos::observe(CargosInternosObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}