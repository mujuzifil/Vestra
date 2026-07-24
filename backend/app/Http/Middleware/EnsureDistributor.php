<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDistributor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isDistributor()) {
            return response()->json([
                'success' => false,
                'message' => 'Distributor access required.',
            ], 403);
        }

        return $next($request);
    }
}
