<?php declare(strict_types=1);

namespace NZTim\Geolocate;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

class GeolocateSessionMiddleware
{
    private Store $session;
    private Geolocate $geolocate;

    public function __construct(Store $session, Geolocate $geolocate)
    {
        $this->session = $session;
        $this->geolocate = $geolocate;
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->session->has('country')) {
            return $next($request);
        }
        $country = $this->geolocate->fromIp($request->ip());
        $this->session->put('country', $country);
        return $next($request);
    }
}
