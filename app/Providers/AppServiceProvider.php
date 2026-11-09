<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pakai view pagination sendiri agar seragam dengan desain aplikasi.
        Paginator::defaultView('vendor.pagination.posyandu');
        Paginator::defaultSimpleView('vendor.pagination.posyandu');
    }
}
