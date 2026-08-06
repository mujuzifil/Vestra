<?php

namespace Tests\Feature\Admin;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStatus;
use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorRequest;
use App\Models\DistributorServiceArea;
use App\Models\User;
use App\Services\Admin\PartnerAdminService;
use App\Services\Catalog\CatalogSyncService;
use App\Services\DistributorOnboardingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DistributorLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);

        config([
            'services.frontend.revalidate_url' => 'https://frontend.test/api/revalidate',
            'services.frontend.revalidate_secret' => 'test-secret',
        ]);

        Http::fake([
            'https://frontend.test/api/revalidate' => Http::response(['revalidated' => true], 200),
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_approve_creates_distributor_credit_branch_service_area_and_public_listing(): void
    {
        $admin = $this->admin();

        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'email' => 'lifecycle-partner@example.com',
            'company_name' => 'Lifecycle Distribution Ltd',
            'country' => 'Uganda',
            'region' => 'Central',
            'target_region' => 'Western',
        ]);

        $distributor = app(DistributorOnboardingService::class)->approve($application, $admin);

        $this->assertDatabaseHas('distributor_requests', [
            'id' => $application->id,
            'status' => DistributorStatus::APPROVED->value,
        ]);

        $this->assertSame($application->id, $distributor->distributor_request_id);
        $this->assertTrue($distributor->user->hasRole('distributor'));

        $this->assertDatabaseHas('credit_accounts', [
            'distributor_id' => $distributor->id,
        ]);

        $this->assertDatabaseHas('distributor_branches', [
            'distributor_id' => $distributor->id,
            'is_default' => true,
            'name' => 'Head Office',
        ]);

        $this->assertGreaterThanOrEqual(1, DistributorServiceArea::query()
            ->where('distributor_id', $distributor->id)
            ->count());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'distributor_approved',
            'user_id' => $admin->id,
        ]);

        $response = $this->getJson('/api/v1/public/distributors');
        $response->assertSuccessful();
        $response->assertJsonFragment(['company_name' => 'Lifecycle Distribution Ltd']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://frontend.test/api/revalidate'
                && ($request['type'] ?? null) === 'distributors'
                && in_array('/where-to-buy', $request['paths'] ?? [], true);
        });
    }

    public function test_orphan_approved_application_is_repaired_via_onboarding(): void
    {
        $admin = $this->admin();

        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::APPROVED,
            'email' => 'orphan-partner@example.com',
            'company_name' => 'Orphan Approved Co',
        ]);

        $this->assertNull(Distributor::query()->where('distributor_request_id', $application->id)->first());

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('approve', $application->id)
            ->assertHasNoErrors();

        $distributor = Distributor::query()->where('distributor_request_id', $application->id)->first();

        $this->assertNotNull($distributor);
        $this->assertDatabaseHas('credit_accounts', ['distributor_id' => $distributor->id]);
        $this->assertDatabaseHas('distributor_branches', ['distributor_id' => $distributor->id, 'is_default' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'distributor_approved_recovered']);
    }

    public function test_repair_lifecycle_command_is_idempotent(): void
    {
        DistributorRequest::factory()->create([
            'status' => DistributorStatus::APPROVED,
            'email' => 'repair-one@example.com',
        ]);

        $existing = Distributor::factory()->create([
            'email' => 'repair-two@example.com',
            'company_name' => 'Needs Branch Co',
        ]);

        Artisan::call('distributors:repair-lifecycle');
        Artisan::call('distributors:repair-lifecycle');

        $this->assertSame(2, Distributor::query()->count());
        $this->assertSame(2, DistributorRequest::query()->where('status', DistributorStatus::APPROVED->value)->count());

        $existing->refresh();
        $this->assertTrue($existing->creditAccount()->exists());
        $this->assertTrue($existing->branches()->exists());
    }

    public function test_suspend_removes_distributor_from_public_listing(): void
    {
        $admin = $this->admin();
        $partnerService = app(PartnerAdminService::class);

        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'email' => 'suspend-test@example.com',
            'company_name' => 'Suspend Test Co',
        ]);

        $distributor = app(DistributorOnboardingService::class)->approve($application, $admin);

        $this->getJson('/api/v1/public/distributors')
            ->assertSuccessful()
            ->assertJsonFragment(['company_name' => 'Suspend Test Co']);

        $partnerService->suspend($distributor, $admin, 'Compliance review');

        $distributor->refresh();
        $this->assertSame(DistributorAccountStatus::SUSPENDED, $distributor->status);
        $this->assertNotNull($distributor->suspended_at);

        $payload = $this->getJson('/api/v1/public/distributors')
            ->assertSuccessful()
            ->json('data');

        $companyNames = collect($payload)->pluck('company_name')->all();
        $this->assertNotContains('Suspend Test Co', $companyNames);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'distributor_suspended',
            'user_id' => $admin->id,
        ]);
    }

    public function test_mark_under_review_and_request_information_update_status(): void
    {
        $admin = $this->admin();
        $service = app(DistributorOnboardingService::class);

        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
        ]);

        $service->markUnderReview($application, $admin);
        $this->assertSame(DistributorStatus::UNDER_REVIEW, $application->fresh()->status);

        $service->requestInformation($application, 'Please upload tax certificate.', $admin);
        $application->refresh();

        $this->assertSame(DistributorStatus::INFORMATION_REQUESTED, $application->status);
        $this->assertSame('Please upload tax certificate.', $application->information_request_notes);
    }

    public function test_reject_persists_reason(): void
    {
        $admin = $this->admin();
        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::UNDER_REVIEW,
        ]);

        app(DistributorOnboardingService::class)->reject($application, 'Incomplete documentation', $admin);

        $application->refresh();
        $this->assertSame(DistributorStatus::REJECTED, $application->status);
        $this->assertSame('Incomplete documentation', $application->rejection_reason);
    }

    public function test_sync_distributors_clears_cache_and_posts_revalidation(): void
    {
        cache()->put('catalog.distributors.active', ['cached'], 3600);

        app(CatalogSyncService::class)->syncDistributors(99);

        $this->assertNull(cache()->get('catalog.distributors.active'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://frontend.test/api/revalidate'
                && ($request['distributor_id'] ?? null) === 99
                && in_array('where-to-buy', $request['tags'] ?? [], true);
        });
    }

    public function test_distributor_default_branch_and_primary_contact_are_relations(): void
    {
        $distributor = Distributor::factory()->create();

        DistributorBranch::factory()->create([
            'distributor_id' => $distributor->id,
            'is_default' => true,
            'name' => 'Default Branch',
        ]);

        $loaded = Distributor::query()->with(['defaultBranch', 'primaryContact'])->find($distributor->id);

        $this->assertNotNull($loaded->defaultBranch);
        $this->assertSame('Default Branch', $loaded->defaultBranch->name);
    }
}
