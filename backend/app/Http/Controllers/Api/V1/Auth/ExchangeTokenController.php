<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\ExchangeToken\ExchangeTokenService;
use App\Services\ExchangeToken\Exceptions\ExpiredExchangeTokenException;
use App\Services\ExchangeToken\Exceptions\InvalidExchangeTokenException;
use App\Services\ExchangeToken\Exceptions\UsedExchangeTokenException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ExchangeTokenController extends Controller
{
    public function __construct(private readonly ExchangeTokenService $exchangeTokenService)
    {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exchange_token' => ['required', 'string', 'size:64'],
        ]);

        $plainTextToken = $validated['exchange_token'];

        try {
            $user = $this->exchangeTokenService->redeem($plainTextToken, $request->ip(), $request->userAgent());
        } catch (InvalidExchangeTokenException $e) {
            AuditService::logAuth(null, 'exchange_token.invalid', $request->ip(), $request->userAgent());

            return response('Invalid exchange token.', 401);
        } catch (ExpiredExchangeTokenException $e) {
            AuditService::logAuth(null, 'exchange_token.expired', $request->ip(), $request->userAgent());

            return response('Exchange token has expired.', 410);
        } catch (UsedExchangeTokenException $e) {
            AuditService::logAuth(null, 'exchange_token.replayed', $request->ip(), $request->userAgent());

            return response('Exchange token has already been used.', 409);
        } catch (\RuntimeException $e) {
            $user = null;
            $message = $e->getMessage();

            if ($message === 'Admin access only.') {
                AuditService::logAuth(null, 'exchange_token.rejected', $request->ip(), $request->userAgent());

                return response('Admin access only.', 403);
            }

            if ($message === 'Account is disabled.') {
                AuditService::logAuth(null, 'exchange_token.rejected', $request->ip(), $request->userAgent());

                return response('Account is disabled.', 403);
            }

            throw $e;
        }

        AuditService::log(
            $user,
            'exchange_token.used',
            $user,
            ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            $request->ip(),
            $request->userAgent()
        );

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        if ($user->mustChangePassword()) {
            return redirect('/admin/force-password-change');
        }

        return redirect('/admin');
    }
}
