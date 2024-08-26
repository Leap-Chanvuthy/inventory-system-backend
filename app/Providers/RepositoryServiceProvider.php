<?php

namespace App\Providers;

use App\Repositories\PurchaseInvoiceDetailRepository;
use App\Repositories\Interfaces\PurchaseInvoiceDetailRepositoryInterface;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Repositories\SupplierRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\RawMaterialRepository;
use App\Repositories\Interfaces\RawMaterialRepositoryInterface;
use App\Repositories\PurchaseInvoiceRepository;
use App\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this -> app -> bind(SupplierRepositoryInterface::class , SupplierRepository::class);
        $this -> app -> bind(UserRepositoryInterface::class , UserRepository::class );
        $this -> app -> bind(RawMaterialRepositoryInterface::class , RawMaterialRepository::class);
        $this -> app -> bind(PurchaseInvoiceRepositoryInterface::class , PurchaseInvoiceRepository::class);
        $this -> app -> bind(PurchaseInvoiceDetailRepositoryInterface::class , PurchaseInvoiceDetailRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
