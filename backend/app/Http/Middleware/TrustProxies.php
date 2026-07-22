<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        // config(), not env(). Under `php artisan config:cache` — which every
        // production deployment runs — env() returns null here, so no proxies
        // would be trusted: X-Forwarded-For would be ignored and every request
        // would appear to originate from nginx, collapsing per-client rate
        // limits into one shared bucket and recording the proxy address in the
        // audit log.
        $proxies = config('app.trusted_proxies');

        $this->proxies = $proxies ? explode(',', (string) $proxies) : null;
    }
}
