<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Force tests to use an isolated in-memory SQLite database and array
        // cache/session regardless of environment variables injected by the
        // Docker container. This prevents the test suite from destroying the
        // shared development database.
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');

        // Ensure the test environment is explicitly recognised as 'testing' so
        // seeders, service providers, and middleware use test-safe behaviour
        // (e.g. AdminUserSeeder resets the bootstrap admin password).
        $app['config']->set('app.env', 'testing');
        $app->detectEnvironment(fn () => 'testing');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiters so throttling state from one test does not leak
        // into the next. RateLimiter::clear('*') is not a valid wildcard, so we
        // clear every named limiter explicitly.
        foreach ($this->limiterNames() as $limiter) {
            RateLimiter::clear($limiter);
        }

        // Ensure the rate limiter uses the array cache store configured for
        // tests. It may have been resolved during boot with a different store
        // (e.g. database), which would cause throttling state to persist across
        // tests even after Cache::flush().
        $rateLimiter = app(\Illuminate\Cache\RateLimiter::class);
        $reflection = new \ReflectionProperty($rateLimiter, 'cache');
        $reflection->setAccessible(true);
        $reflection->setValue($rateLimiter, app('cache')->driver('array'));

        // Flush the in-memory array cache so cached settings/auth/state do not
        // persist between tests. Rate limiters use the cache, so this also
        // resets throttling state between tests.
        Cache::flush();

        // Reset Spatie permission cached data so role/permission checks do not
        // use state from previous tests.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Reset the application environment to testing in case a previous test
        // changed it (e.g. ProductionBootstrapPasswordTest sets it to production).
        $this->app['env'] = 'testing';
        $this->app['config']->set('app.env', 'testing');
    }

    /**
     * @return array<int, string>
     */
    private function limiterNames(): array
    {
        return [
            'api',
            'api:auth',
            'login',
            'register',
            'change-password',
            'contact',
            'payment',
            'distributor',
            'feedback',
            'webhook',
        ];
    }
}
