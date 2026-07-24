<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Binding Location Repository
        $this->app->bind(
            \App\Repositories\Contracts\LocationRepositoryInterface::class,
            \App\Repositories\Eloquent\LocationRepository::class
        );

        // Binding Item Repository (TAMBAHKAN INI)
        $this->app->bind(
            \App\Repositories\Contracts\ItemRepositoryInterface::class,
            \App\Repositories\Eloquent\ItemRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\BorrowingRepositoryInterface::class,
            \App\Repositories\Eloquent\BorrowingRepository::class
        );

        // Binding Maintenance Repository
        $this->app->bind(
            \App\Repositories\Contracts\MaintenanceRepositoryInterface::class,
            \App\Repositories\Eloquent\MaintenanceRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
