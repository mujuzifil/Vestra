<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustProxies;
use App\Services\AuditService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\HandleCors;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            HandleCors::class,
            TrustProxies::class,
            SecurityHeaders::class,
        ]);

        // Global middleware for web routes
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // The exchange endpoint is called via a cross-origin form POST from the
        // public frontend to establish a Filament web session. The exchange token
        // itself is the credential, so CSRF protection is not required here.
        $middleware->validateCsrfTokens(except: [
            '/api/v1/auth/exchange',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return JSON for API routes
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
            return null;
        });

        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
            return null;
        });

        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                ], 403);
            }
            return null;
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*')) {
                AuditService::log(
                    $request->user(),
                    'authorization.denied',
                    null,
                    [
                        'method' => $request->method(),
                        'url' => $request->url(),
                        'message' => $e->getMessage(),
                    ],
                    $request->ip(),
                    $request->userAgent()
                );

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden.',
                ], 403);
            }
            return null;
        });

        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
            return null;
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }
            return null;
        });

        $exceptions->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                if ($request->is('api/*/auth/login')) {
                    AuditService::logAuth(null, 'login.lockout', $request->ip(), $request->userAgent());
                }

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Too many attempts.',
                ], 429);
            }
            return null;
        });

        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $debug = config('app.debug', false);
                return response()->json([
                    'success' => false,
                    'message' => $debug ? $e->getMessage() : 'An unexpected error occurred.',
                    ...($debug ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : []),
                ], 500);
            }
            return null;
        });

        // Log all exceptions in production
        if (app()->environment('production')) {
            $exceptions->report(function (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            });
        }
    })->create();
