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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        // This works for @can() checks, but middleware role:admin still needs explicit role assignment
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\GpsTcpServer::class,
            ]);
        }

        \App\Models\FuelSensor::observe(\App\Observers\FuelSensorObserver::class);
    }
}
