<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Contracts\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );

        $this->app->bind(
            \App\Services\Contracts\RecycleServiceInterface::class,
            \App\Services\RecycleService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
