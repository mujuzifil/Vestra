<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Health, readiness and liveness probes.
 *
 * These are consumed by the Docker HEALTHCHECK, the nginx upstream and any
 * external uptime monitor, so the HTTP status code — not the body — is the
 * contract. A failing dependency MUST produce 503; orchestrators do not parse
 * JSON before deciding whether to route traffic to a container.
 */
class HealthController extends Controller
{
    use RespondsWithJson;

    /**
     * Full health check: database, storage and cache.
     *
     * 200 when every dependency is reachable, 503 otherwise.
     */
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
        ];

        return $this->healthResponse($checks, 'status', 'healthy', 'unhealthy');
    }

    /**
     * Readiness probe: is this instance ready to serve traffic?
     *
     * Checks every backing service the request path actually needs. Redis is
     * included because cache, queue and sessions all run through it — an
     * instance with Redis down cannot serve an authenticated request even
     * though PHP itself is fine.
     */
    public function readiness(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'redis' => $this->checkRedis(),
        ];

        return $this->healthResponse($checks, 'ready', true, false);
    }

    /**
     * Liveness probe: is the PHP process itself alive?
     *
     * Deliberately checks no dependencies. A liveness probe that fails on a
     * transient database blip would cause the orchestrator to kill and restart
     * an otherwise healthy container, turning a brief outage into a crash loop.
     */
    public function liveness(): JsonResponse
    {
        return $this->successResponse([
            'alive' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Build a probe response, returning 503 when any check failed.
     *
     * @param  array<string, bool>  $checks
     */
    private function healthResponse(array $checks, string $statusKey, mixed $okValue, mixed $failValue): JsonResponse
    {
        $healthy = ! in_array(false, $checks, true);

        return response()->json([
            'success' => $healthy,
            'data' => [
                $statusKey => $healthy ? $okValue : $failValue,
                'checks' => $checks,
                'timestamp' => now()->toIso8601String(),
            ],
            'message' => $healthy
                ? 'All systems operational.'
                : 'One or more health checks failed.',
        ], $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::connection()->select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkStorage(): bool
    {
        try {
            $path = 'health-check-'.uniqid('', true).'.txt';
            Storage::put($path, 'ok');
            $ok = Storage::get($path) === 'ok';
            Storage::delete($path);

            return $ok;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            $key = 'health-check-'.uniqid('', true);
            cache()->put($key, 'ok', 10);
            $value = cache()->get($key);
            cache()->forget($key);

            return $value === 'ok';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Probe Redis directly. Reports healthy when Redis is not the configured
     * backend, so the check is meaningful in production without failing the
     * array-driver test environment.
     */
    private function checkRedis(): bool
    {
        $usesRedis = in_array('redis', [
            config('cache.default'),
            config('queue.default'),
            config('session.driver'),
        ], true);

        if (! $usesRedis) {
            return true;
        }

        try {
            return (bool) Redis::connection()->ping();
        } catch (\Throwable) {
            return false;
        }
    }
}
