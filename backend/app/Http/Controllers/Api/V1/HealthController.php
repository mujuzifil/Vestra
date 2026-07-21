<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    use RespondsWithJson;

    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
        ];

        $healthy = ! in_array(false, $checks, true);

        return $this->successResponse(
            [
                'status' => $healthy ? 'healthy' : 'unhealthy',
                'checks' => $checks,
                'timestamp' => now()->toIso8601String(),
            ],
            $healthy ? 'All systems operational.' : 'One or more health checks failed.'
        );
    }

    public function readiness(): JsonResponse
    {
        return $this->successResponse([
            'ready' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function liveness(): JsonResponse
    {
        return $this->successResponse([
            'alive' => true,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function checkStorage(): bool
    {
        try {
            $path = 'health-check-' . time() . '.txt';
            Storage::put($path, 'ok');
            Storage::delete($path);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            $key = 'health-check-' . time();
            cache()->put($key, 'ok', 10);
            $value = cache()->get($key);
            cache()->forget($key);
            return $value === 'ok';
        } catch (\Exception) {
            return false;
        }
    }
}
