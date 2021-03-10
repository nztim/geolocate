<?php

use NZTim\SimpleHttp\Http;

function geolocate(string $ip, int $timeout = 5): string
{
    // Skip unit tests
    if (app()->runningUnitTests()) {
        return 'NZ';
    }
    // Validate $ip
    $ip = trim($ip);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '??';
    }
    // Get result
    $domain = config('services.geo_domain', 'example.org');
    $url = "https://{$domain}/locate?ip={$ip}";
    $key = 'geo-' . md5($ip);
    if (cache()->has($key)) {
        return cache($key);
    }
    try {
        $response = (new Http())->timeout($timeout)->get($url);
    } catch (Throwable $e) {
        log_warning('comms', 'Geolocate helper failed: ' . $e->getMessage());
        return '??';
    }
    if (!$response->isOk()) {
        log_warning('comms', 'Geolocate helper failed, response code: ' . $response->status());
        return '??';
    }
    $result = str_limit(trim($response->body()), 2);
    cache()->put($key, $result, now()->addWeek());
    return $result;
}
