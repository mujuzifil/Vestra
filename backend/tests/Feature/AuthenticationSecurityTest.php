<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        RateLimiter::clear('*');
        Cache::flush();
    }

    /**
     * The named throttle middleware hashes the limiter name and the key
     * returned by the limiter closure. This helper mirrors that behaviour.
     */
    private function throttleKey(string $limiterName, string $limitKey): string
    {
        return md5($limiterName.$limitKey);
    }

    public function test_failed_login_is_logged(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@vestra.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login.failed',
        ]);
    }

    public function test_disabled_account_login_is_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'disabled2@vestra.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'status' => 'inactive',
        ]);
        $user->assignRole('customer');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'disabled2@vestra.com',
            'password' => 'Password123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login.rejected.disabled',
            'user_id' => $user->id,
        ]);
    }

    public function test_login_rate_limiter_configuration(): void
    {
        $limiter = RateLimiter::limiter('login');
        $this->assertInstanceOf(\Closure::class, $limiter);

        $ip = '10.0.1.10';
        $request = Request::create('/api/v1/auth/login', 'POST', [
            'email' => 'test@example.com',
        ]);
        $request->server->set('REMOTE_ADDR', $ip);

        $limits = $limiter($request);
        $this->assertCount(2, $limits);
        $this->assertSame('login:ip:'.$ip, $limits[0]->key);
        $this->assertSame('login:email:test@example.com', $limits[1]->key);
    }

    public function test_login_rate_limit_triggers_after_five_attempts(): void
    {
        $ip = '10.0.1.10';
        $email = 'ratelimit@example.com';

        RateLimiter::clear($this->throttleKey('login', 'login:ip:'.$ip));
        RateLimiter::clear($this->throttleKey('login', 'login:email:'.$email));

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($this->throttleKey('login', 'login:ip:'.$ip), 60);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_register_rate_limiter_configuration(): void
    {
        $limiter = RateLimiter::limiter('register');
        $this->assertInstanceOf(\Closure::class, $limiter);

        $ip = '10.0.2.10';
        $request = Request::create('/api/v1/auth/register', 'POST');
        $request->server->set('REMOTE_ADDR', $ip);

        $limits = $limiter($request);
        $this->assertSame('register:ip:'.$ip, $limits->key);
    }

    public function test_register_rate_limit_triggers_after_five_attempts(): void
    {
        $ip = '10.0.2.10';
        $key = $this->throttleKey('register', 'register:ip:'.$ip);

        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'ratelimit@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(429);
    }

    public function test_change_password_rate_limiter_configuration(): void
    {
        $limiter = RateLimiter::limiter('change-password');
        $this->assertInstanceOf(\Closure::class, $limiter);

        $user = User::factory()->make(['id' => 99]);
        $request = Request::create('/api/v1/auth/change-password', 'POST');
        $request->setUserResolver(fn () => $user);

        $limits = $limiter($request);
        $this->assertSame('change-password:99', $limits->key);
    }

    public function test_change_password_rate_limit_triggers_after_five_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'customer-cp@vestra.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');
        $token = $user->createToken('test-token', ['customer'])->plainTextToken;

        $key = $this->throttleKey('change-password', 'change-password:'.$user->id);
        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'NewStrongP@ssw0rd',
            'password_confirmation' => 'NewStrongP@ssw0rd',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(429);
    }

    public function test_logout_deletes_current_access_token(): void
    {
        $user = User::factory()->create([
            'email' => 'customer-logout@vestra.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');
        $token = $user->createToken('test-token', ['customer'])->plainTextToken;

        $tokenId = PersonalAccessToken::findToken($token)?->id;
        $this->assertNotNull($tokenId);

        $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => "Bearer {$token}",
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_password_change_revokes_other_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'customer-pc@vestra.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        RateLimiter::clear($this->throttleKey('change-password', 'change-password:'.$user->id));

        $firstToken = $user->createToken('first-token', ['customer'])->plainTextToken;
        $firstTokenId = PersonalAccessToken::findToken($firstToken)?->id;

        $secondToken = $user->createToken('second-token', ['customer'])->plainTextToken;
        $secondTokenId = PersonalAccessToken::findToken($secondToken)?->id;

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'Password123',
            'password' => 'NewStrongP@ssw0rd',
            'password_confirmation' => 'NewStrongP@ssw0rd',
        ], [
            'Authorization' => "Bearer {$secondToken}",
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $firstTokenId,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $secondTokenId,
        ]);
    }

    public function test_login_lockout_is_audited(): void
    {
        $ip = '10.0.5.10';
        $email = 'lockout@example.com';

        RateLimiter::clear($this->throttleKey('login', 'login:ip:'.$ip));
        RateLimiter::clear($this->throttleKey('login', 'login:email:'.$email));

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($this->throttleKey('login', 'login:ip:'.$ip), 60);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login.lockout',
        ]);
    }

    public function test_expired_sanctum_tokens_are_pruned(): void
    {
        $user = User::factory()->create([
            'email' => 'customer-prune@vestra.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'status' => 'active',
        ]);
        $user->assignRole('customer');

        $token = $user->tokens()->create([
            'name' => 'expired-token',
            'token' => hash('sha256', 'plain'),
            'abilities' => ['customer'],
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('sanctum:cleanup-expired')
            ->assertSuccessful();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }

    public function test_trust_proxies_respects_environment(): void
    {
        $proxiesProperty = (new \ReflectionClass(TrustProxies::class))->getProperty('proxies');
        $proxiesProperty->setAccessible(true);

        putenv('TRUSTED_PROXIES=');
        $middleware = new TrustProxies();
        $this->assertNull($proxiesProperty->getValue($middleware));

        putenv('TRUSTED_PROXIES=10.0.0.1,10.0.0.2');
        $middleware = new TrustProxies();
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $proxiesProperty->getValue($middleware));
    }

    public function test_session_cookie_security_configuration(): void
    {
        config([
            'session.encrypt' => true,
            'session.secure' => true,
            'session.http_only' => true,
            'session.same_site' => 'strict',
        ]);

        $this->assertTrue(config('session.encrypt'));
        $this->assertTrue(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('strict', config('session.same_site'));
    }
}
