<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\CustomerFeedback;
use App\Models\DistributorRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Observers\ContactMessageObserver;
use App\Observers\CustomerFeedbackObserver;
use App\Observers\CustomerObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Repositories\CategoryRepository;
use App\Repositories\ContactRepository;
use App\Repositories\DistributorRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SettingRepository;
use App\Services\AuditService;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CategoryRepository::class, function ($app) {
            return new CategoryRepository(new Category);
        });

        $this->app->singleton(ProductRepository::class, function ($app) {
            return new ProductRepository(new Product);
        });

        $this->app->singleton(ContactRepository::class, function ($app) {
            return new ContactRepository(new ContactMessage);
        });

        $this->app->singleton(DistributorRepository::class, function ($app) {
            return new DistributorRepository(new DistributorRequest);
        });

        $this->app->singleton(SettingRepository::class, function ($app) {
            return new SettingRepository(new Setting);
        });

        $this->app->singleton(\App\Contracts\PaymentGatewayInterface::class, function ($app) {
            return new \App\Services\FlutterwaveGateway();
        });
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(CustomerObserver::class);
        ContactMessage::observe(ContactMessageObserver::class);
        CustomerFeedback::observe(CustomerFeedbackObserver::class);

        // Production is served exclusively over HTTPS behind a TLS-terminating
        // proxy. URL generation that runs before the TrustProxies middleware —
        // Filament panel registration, queued mail, console commands — cannot
        // see X-Forwarded-Proto and would otherwise emit http:// links (e.g.
        // the admin panel favicon). Force the scheme for all generators.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->enforceBootstrapPasswordNotDefault();
    }

    protected function enforceBootstrapPasswordNotDefault(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        // Compare against the SHIPPED DEFAULT, not BOOTSTRAP_ADMIN_PASSWORD.
        //
        // The risk this guard exists to stop is the publicly-known credential
        // in AdminUserSeeder::DEFAULT_PASSWORD reaching production. An operator
        // who sets a strong BOOTSTRAP_ADMIN_PASSWORD is not that risk.
        //
        // Checking the configured value instead would brick every first
        // deployment: `db:seed` creates the administrator with exactly that
        // password, so the guard would fire immediately and 500 every request —
        // including the admin panel needed to change it, and the health
        // endpoints needed to diagnose it. First login is already gated by
        // `force_password_change_at`, which the seeder sets; that is the
        // mechanism that makes the operator rotate it.
        // A compile-time constant, so this one is safe from the env()/config:cache
        // hazard that affects the configured password. See config/app.php.
        $defaultPassword = AdminUserSeeder::DEFAULT_PASSWORD;
        $defaultPasswordInUse = false;

        try {
            // There is nothing to verify until the database is reachable and
            // migrated. This runs in provider boot, so it also fires during
            // `composer dump-autoload` (via package:discover) at image build
            // time and during `artisan migrate` — both legitimately have no
            // users table. Probing first keeps those paths working; it does not
            // weaken the guard, because an unreachable database cannot be
            // serving an admin panel either.
            DB::connection()->getPdo();

            if (! Schema::hasTable('users')) {
                return;
            }

            $user = User::where('email', 'admin@vestra.com')->first();

            if (! $user) {
                return;
            }

            if (! Hash::check($defaultPassword, $user->password)) {
                return;
            }

            $defaultPasswordInUse = true;

            AuditService::log(
                $user,
                'security.default_password_in_use',
                $user,
                ['message' => 'Default bootstrap administrator password is still in use in production.']
            );

            Log::critical('Security: Default bootstrap administrator password is still in use in production.');
        } catch (\Throwable $e) {
            // Inability to VERIFY is not evidence of a violation. Log it and let
            // the application boot — a database outage must not also take down
            // every request, including the health probes used to diagnose it.
            //
            // The abort below is deliberately outside this block. PDOException
            // extends RuntimeException, so re-throwing RuntimeException from
            // here would propagate every transient database error out of
            // provider boot and 500 the entire application.
            Log::error('Unable to verify bootstrap administrator password: '.$e->getMessage());

            return;
        }

        // Refuse to serve while the shipped default password is still valid.
        if ($defaultPasswordInUse) {
            throw new \RuntimeException(
                'Default bootstrap administrator password detected in production. Change the password immediately and re-deploy.'
            );
        }
    }
}
