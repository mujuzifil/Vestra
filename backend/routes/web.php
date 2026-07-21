<?php

use App\Http\Controllers\Api\V1\Auth\ExchangeTokenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'api' => url('/api/v1'),
        'admin' => url('/admin'),
    ]);
});

Route::middleware(['web'])
    ->post('/api/v1/auth/exchange', [ExchangeTokenController::class, 'store'])
    ->name('auth.exchange');
