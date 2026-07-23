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
        // TrustProxies is GLOBAL, not api-group-only: the Filament panel (/admin)
        // runs through the web group. Without it there, web requests ignore
        // X-Forwarded-Proto behind the TLS-terminating proxy and URL generation
        // (login redirects, asset URLs) falls back to http://, breaking the
        // admin panel over HTTPS.
        $middleware->prepend(TrustProxies::class);

        $middleware->api(prepend: [
            HandleCors::class,
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

        // Never redirect an unauthenticated API request to a login page.
        //
        // The Authenticate middleware resolves this BEFORE the exception
        // handler runs, so shouldRenderJsonWhen() cannot rescue it. For a
        // request that did not send `Accept: application/json`, the default
        // resolves route('login') — which this API-only application does not
        // define — and the resulting RouteNotFoundException surfaces as a 500
        // instead of a 401.
        //
        // Returning null makes the middleware throw AuthenticationException,
        // which the handler below renders as a proper 401.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('api/*') ? null : route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Treat every /api/* request as a JSON client, regardless of whether it
        // sent an Accept header.
        //
        // Without this, an unauthenticated request that does not ask for JSON
        // takes Laravel's web branch and redirects to route('login') — which
        // this API-only application does not define — producing a 500
        // "unexpected error" instead of a 401. The renderable callbacks below
        // never get the chance to run, because the RouteNotFoundException is
        // raised first.
        $exceptions->shouldRenderJsonWhen(
            fn ($request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

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
