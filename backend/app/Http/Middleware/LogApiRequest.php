<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = (int) round((microtime(true) - $start) * 1000);

        $path = $request->path();
        if (str_starts_with($path, 'api/')) {
            $path = substr($path, 4);
        }

        ApiRequestLog::create([
            'user_id' => $request->user()?->id,
            'method' => $request->getMethod(),
            'path' => '/'.$path,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 512),
        ]);

        return $response;
    }
}
