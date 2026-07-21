<?php

namespace App\Providers;

use App\Listeners\LogAdminFailedLogin;
use App\Listeners\LogAdminLogin;
use App\Listeners\LogAdminLogout;
use App\Listeners\UpdateAdminSessionActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogAdminLogin::class,
        ],
        Failed::class => [
            LogAdminFailedLogin::class,
        ],
        Logout::class => [
            LogAdminLogout::class,
        ],
        RouteMatched::class => [
            UpdateAdminSessionActivity::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
