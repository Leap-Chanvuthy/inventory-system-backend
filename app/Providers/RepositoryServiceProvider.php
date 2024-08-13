<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Repositories\SupplierRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
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
    
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
