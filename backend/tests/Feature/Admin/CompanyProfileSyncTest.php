<?php

namespace Tests\Feature\Admin;

use App\Models\DistributorRequest;
use App\Models\User;
use App\Services\CompanyProfileService;
use App\Services\DistributorOnboardingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_distributor_approval_creates_company_profile(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $application = DistributorRequest::factory()->create([
            'status' => 'pending',
            'email' => 'partner-sync@example.com',
            'company_name' => 'Sync Partner Co',
        ]);

        app(DistributorOnboardingService::class)->approve($application, $admin);

        $user = User::query()->where('email', 'partner-sync@example.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('company_profiles', [
            'user_id' => $user->id,
            'company_name' => 'Sync Partner Co',
        ]);
    }

    public function test_guest_quote_resolve_creates_company(): void
    {
        $profile = app(CompanyProfileService::class)->resolveForQuote(null, [
            'email' => 'guest-quote@example.com',
            'full_name' => 'Guest User',
            'company_name' => 'Guest Co',
            'phone' => '+256700000001',
        ]);

        $this->assertNotNull($profile);
        $this->assertSame('Guest Co', $profile->company_name);
        $this->assertDatabaseHas('users', ['email' => 'guest-quote@example.com']);
    }
}
