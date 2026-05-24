<?php

namespace App\Providers;

use App\Services\PopulationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind PopulationService so its constructor gets the API key injected cleanly
        $this->app->singleton(PopulationService::class, function () {
            return new PopulationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
