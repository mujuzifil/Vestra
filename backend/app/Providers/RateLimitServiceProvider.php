<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Public API routes: 60 requests per minute per IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Authenticated API routes: 120 requests per minute per user
        RateLimiter::for('api:auth', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();
            return Limit::perMinute(120)->by($key);
        });

        // Login attempts: 5 per minute per IP and 5 per minute per email
        RateLimiter::for('login', function (Request $request) {
            $ipKey = 'login:ip:'.$request->ip();
            $emailKey = 'login:email:'.($request->input('email') ?: $request->ip());

            return [
                Limit::perMinute(5)->by($ipKey),
                Limit::perMinute(5)->by($emailKey),
            ];
        });

        // Registration attempts: 5 per minute per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by('register:ip:'.$request->ip());
        });

        // Change-password attempts: 5 per minute per user
        RateLimiter::for('change-password', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(5)->by('change-password:'.$key);
        });

        // Contact form: 3 per minute per IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by('contact:ip:'.$request->ip());
        });

        // Payment initiation: 10 per minute per user
        RateLimiter::for('payment', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();
            return Limit::perMinute(10)->by($key);
        });

        // Public distributor request form: 3 per minute per IP
        RateLimiter::for('distributor', function (Request $request) {
            return Limit::perMinute(3)->by('distributor:ip:'.$request->ip());
        });

        // Public feedback form: 3 per minute per IP
        RateLimiter::for('feedback', function (Request $request) {
            return Limit::perMinute(3)->by('feedback:ip:'.$request->ip());
        });

        // Payment webhook callback: 30 per minute per IP
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(30)->by('webhook:ip:'.$request->ip());
        });
    }
}
