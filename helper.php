<?php

use NZTim\SimpleHttp\Http;

function geolocate(string $ip, int $timeout = 5): string
{
    $ip = trim($ip);
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '??';
    }
    $domain = config('services.geo_domain', 'example.org');
    $url = "https://{$domain}/locate?ip={$ip}";
    $response = (new Http())
        ->timeout($timeout)
        ->get($url);
    if (!$response->isOk()) {
        return '??';
    }
    return str_limit($response->body(), 2);
}
