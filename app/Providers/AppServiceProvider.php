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
    $this->app->singleton('db.connector.pgsql', function () {
        return new \App\Database\Connectors\NeonPostgresConnector();
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
