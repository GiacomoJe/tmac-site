<?php

namespace App\Providers;

use App\Services\RdStation;
use App\Services\SeoMeta;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeoMeta::class);

        $this->app->singleton(RdStation::class, function () {
            return new RdStation(
                publicToken: env('RDSTATION_PUBLIC_TOKEN'),
                enabled: filter_var(env('RDSTATION_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
            );
        });
    }

    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
