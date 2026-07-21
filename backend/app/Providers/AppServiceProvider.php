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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $this->enforceBootstrapPasswordNotDefault();
    }

    protected function enforceBootstrapPasswordNotDefault(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $bootstrapPassword = env('BOOTSTRAP_ADMIN_PASSWORD', 'Admin@12345');

        try {
            $user = User::where('email', 'admin@vestra.com')->first();

            if (! $user) {
                return;
            }

            if (! Hash::check($bootstrapPassword, $user->password)) {
                return;
            }

            AuditService::log(
                $user,
                'security.default_password_in_use',
                $user,
                ['message' => 'Default bootstrap administrator password is still in use in production.']
            );

            Log::critical('Security: Default bootstrap administrator password is still in use in production.');

            // Prevent application startup until the default password is changed.
            throw new \RuntimeException(
                'Default bootstrap administrator password detected in production. Change the password immediately and re-deploy.'
            );
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Unable to verify bootstrap administrator password: '.$e->getMessage());
        }
    }
}
