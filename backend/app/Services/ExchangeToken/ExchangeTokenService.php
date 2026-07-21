<?php

namespace App\Services\ExchangeToken;

use App\Models\ExchangeToken;
use App\Models\User;
use App\Services\ExchangeToken\Exceptions\ExpiredExchangeTokenException;
use App\Services\ExchangeToken\Exceptions\InvalidExchangeTokenException;
use App\Services\ExchangeToken\Exceptions\UsedExchangeTokenException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExchangeTokenService
{
    private const TTL_SECONDS = 30;

    public function create(User $user, ?string $ip = null, ?string $userAgent = null): array
    {
        $plainText = Str::random(64);

        $token = ExchangeToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainText),
            'expires_at' => now()->addSeconds(self::TTL_SECONDS),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        return [
            'plain_text' => $plainText,
            'exchange_token' => $token,
        ];
    }

    public function redeem(string $plainTextToken, ?string $ip = null, ?string $userAgent = null): User
    {
        $hash = hash('sha256', $plainTextToken);

        /** @var ExchangeToken|null $token */
        $token = ExchangeToken::where('token_hash', $hash)->first();

        if (! $token) {
            throw new InvalidExchangeTokenException();
        }

        if ($token->isExpired()) {
            $token->delete();
            throw new ExpiredExchangeTokenException();
        }

        if ($token->isUsed()) {
            throw new UsedExchangeTokenException();
        }

        $user = $token->user;

        if (! $user) {
            $token->delete();
            throw new InvalidExchangeTokenException();
        }

        if (! $user->isAdmin()) {
            throw new \RuntimeException('Admin access only.');
        }

        if (! $user->isActive()) {
            throw new \RuntimeException('Account is disabled.');
        }

        // Mark the token as used. Single-use enforcement is handled by the
        // used_at timestamp; cleanup removes consumed tokens later.
        $token->update(['used_at' => now()]);

        return $user;
    }

    public function pruneExpired(): int
    {
        return ExchangeToken::expired()->orWhereNotNull('used_at')->delete();
    }
}
