<?php declare(strict_types=1);

namespace NZTim\Geolocate;

use Illuminate\Support\ServiceProvider;

class GeolocateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            UpdateGeoDatabaseCommand::class,
        ]);
    }
}
