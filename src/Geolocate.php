<?php declare(strict_types=1);

namespace NZTim\Geolocate;

use GeoIp2\Database\Reader;
use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Throwable;

class Geolocate
{
    private Request $request;
    private CacheManager $cache;

    public function __construct(Request $request, CacheManager $cache)
    {
        $this->request = $request;
        $this->cache = $cache;
    }

    public function cached(string $ip = null): string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = $this->request->ip();
        }
        $key = "geo-{$ip}";
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }
        $geo = $this->fromIp($ip);
        $this->cache->put($key, $geo, now()->addWeek());
        return $geo;
    }

    public function fromIp(string $ip): string
    {
        if (app()->environment() !== 'production') {
            return 'NZ';
        }
        try {
            $reader = new Reader(storage_path('app/geolite2/country.mmdb'));
        } catch (InvalidDatabaseException $e) {
            log_error('laravel', 'Invalid Maxmind Geoip database! Unable to geolocate until this is fixed.');
            return '??';
        }
        try {
            $country = $reader->country($ip);
        } catch (Throwable $e) {
            log_warning('geo', 'Unable to obtain country for ip: ' . $ip);
            return '??';
        }
        return $country->country->isoCode ?? '??';
    }

    public function isNz(string $ip = null): bool
    {
        return $this->cached($ip) === 'NZ';
    }

    public function overseas(string $ip = null): bool
    {
        return !$this->isNz($ip);
    }
}
