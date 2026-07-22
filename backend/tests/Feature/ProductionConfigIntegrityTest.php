<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards against a class of bug that only appears in production.
 *
 * `php artisan config:cache` — which every production deployment runs — makes
 * `env()` return null everywhere outside `config/*.php`. Code that calls
 * `env()` at runtime therefore works perfectly in local and test environments,
 * where the config is never cached, and silently falls back to its default in
 * production.
 *
 * Phase 15 found three live instances of this:
 *
 *  - TrustProxies read TRUSTED_PROXIES via env(), so production trusted no
 *    proxies at all: X-Forwarded-For was ignored, every request appeared to
 *    come from nginx, per-client rate limits collapsed into one shared bucket
 *    and audit logs recorded the proxy address instead of the client.
 *
 *  - AdminUserSeeder read BOOTSTRAP_ADMIN_PASSWORD via env(), so the first
 *    production seed created the administrator with the shipped default
 *    password regardless of what the operator configured — which then tripped
 *    the boot guard and 500'd every request, including the health endpoints.
 *
 *  - DatabaseSeeder read DEMO_DATA via env().
 *
 * These assertions are cheap. The failure they prevent is not.
 */
class ProductionConfigIntegrityTest extends TestCase
{
    /**
     * Runtime code must read deployment settings through config(), never env().
     */
    public function test_no_runtime_code_calls_env_directly(): void
    {
        $roots = [
            base_path('app'),
            base_path('database/seeders'),
            base_path('database/factories'),
            base_path('routes'),
        ];

        $offenders = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                foreach (file($file->getPathname()) as $number => $line) {
                    // Ignore comments — several of these files explain the hazard.
                    $trimmed = ltrim($line);
                    if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                        continue;
                    }

                    if (preg_match('/(?<![\w>$])env\s*\(/', $line)) {
                        $offenders[] = sprintf(
                            '%s:%d — %s',
                            str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()),
                            $number + 1,
                            trim($line)
                        );
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "env() must not be called outside config/*.php — it returns null once the config is cached.\n"
            ."Move the value into config/app.php and read it with config().\n\nFound:\n  "
            .implode("\n  ", $offenders)
        );
    }

    /**
     * The settings Phase 15 migrated must remain resolvable through config().
     */
    public function test_deployment_settings_are_exposed_through_config(): void
    {
        foreach ([
            'app.trusted_proxies',
            'app.bootstrap_admin_password',
            'app.reset_bootstrap_admin',
            'app.demo_data',
        ] as $key) {
            $this->assertTrue(
                app('config')->has($key),
                "config('{$key}') is missing. Runtime code depends on it being defined in config/app.php."
            );
        }
    }

    /**
     * Trusted proxies must survive config caching, since that is exactly the
     * condition under which the original bug appeared.
     */
    public function test_trusted_proxies_resolves_from_cached_config(): void
    {
        config(['app.trusted_proxies' => '10.0.0.1,10.0.0.2']);

        $middleware = new \App\Http\Middleware\TrustProxies();

        $reflection = new \ReflectionProperty($middleware, 'proxies');
        $reflection->setAccessible(true);

        $this->assertSame(['10.0.0.1', '10.0.0.2'], $reflection->getValue($middleware));
    }
}
