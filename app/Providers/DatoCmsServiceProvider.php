<?php

namespace App\Providers;

use App\Services\DatoCms\DatoCmsClient;
use Illuminate\Support\ServiceProvider;

class DatoCmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/datocms.php', 'datocms'
        );

        $this->app->singleton(DatoCmsClient::class, function ($app) {
            return new DatoCmsClient(
                config('datocms.api_token'),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/datocms.php' => config_path('datocms.php'),
            ], 'datocms-config');
        }
    }
}
