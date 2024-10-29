<?php

namespace App\Providers;

use App\Models\PurchaseInvoice;
use Illuminate\Support\ServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Models\RawMaterial;
use App\Observers\PurchaseInvoiceObserver;
use App\Observers\RawMaterialObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this -> app -> register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RawMaterial::observe(RawMaterialObserver::class);
        PurchaseInvoice::observe(PurchaseInvoiceObserver::class);
    }
}
