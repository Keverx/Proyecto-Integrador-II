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

        $this->app->bind(
            \App\Services\Contracts\RecyclingHistoryQueriesInterface::class,
            \App\Services\RecyclingHistoryQueries::class
        );

        $this->app->bind(
            \App\Services\Contracts\HistoryCalculatorInterface::class,
            \App\Services\HistoryCalculator::class
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
