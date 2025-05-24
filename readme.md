# Geolocate

* Downloads Maxmind geoip database and keeps it up to date with scheduler entry.
* Provides Geolocate class and helper which accepts an IP address and returns a country code string.

### Configuration

* Set access key: `services.maxmind_key`
* Register service provider: `GeolocateServiceProvider::class`
* Schedule database update via console kernel: `$schedule->command(UpdateGeoDatabaseCommand::class)->weekly()->mondays()->at('5:30');` 
* Register `GeolocateSessionMiddleware::class` in Http\Kernel for automatic recording of country code in session `session('country')`.
