<?php

namespace Tests\Feature\Admin;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStatus;
use App\Filament\Pages\Distributors\ActivePartnersPage;
use App\Filament\Pages\Distributors\ApplicationsPage;
use App\Filament\Pages\Distributors\TerritoriesPage;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorRequest;
use App\Models\User;
use App\Services\Admin\PartnerAdminService;
use App\Services\DistributorCoverageSync;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CoverageSyncAndDeletesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function test_profile_update_syncs_service_area_and_coordinates_for_coverage(): void
    {
        $admin = $this->admin();
        $distributor = Distributor::factory()->create([
            'status' => DistributorAccountStatus::ACTIVE,
            'district' => null,
            'city' => null,
            'address' => null,
        ]);

        app(PartnerAdminService::class)->updateProfile($distributor, [
            'district' => 'Kampala',
            'city' => 'Nakawa',
            'address' => '8H4C+6JR, Kampala',
            'country' => 'Uganda',
        ], $admin);

        $distributor->refresh();

        $this->assertDatabaseHas('distributor_service_areas', [
            'distributor_id' => $distributor->id,
            'region' => 'Central',
            'district' => 'Kampala',
            'status' => 'covered',
        ]);

        $branch = $distributor->branches()->where('is_default', true)->first()
            ?? $distributor->branches()->first();

        $this->assertNotNull($branch);
        $this->assertNotNull($branch->latitude);
        $this->assertNotNull($branch->longitude);

        $this->getJson('/api/v1/public/distributors/coverage')
            ->assertSuccessful()
            ->assertJsonPath('data.Central.0.district', 'Kampala');
    }

    public function test_territories_map_mode_backfills_coordinates(): void
    {
        $admin = $this->admin();
        $distributor = Distributor::factory()->create([
            'status' => DistributorAccountStatus::ACTIVE,
            'district' => 'Gulu',
            'city' => 'Pece',
            'address' => 'Gulu town',
        ]);

        DistributorBranch::factory()->create([
            'distributor_id' => $distributor->id,
            'name' => 'Head Office',
            'district' => 'Gulu',
            'city' => 'Pece',
            'country' => 'Uganda',
            'latitude' => null,
            'longitude' => null,
            'is_default' => true,
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(TerritoriesPage::class)
            ->call('setViewMode', 'map')
            ->assertSet('viewMode', 'map');

        $branch = $distributor->branches()->first();
        $this->assertNotNull($branch->fresh()->latitude);
        $this->assertNotNull($branch->fresh()->longitude);
    }

    public function test_admin_can_delete_application(): void
    {
        $admin = $this->admin();
        $application = DistributorRequest::factory()->create([
            'status' => DistributorStatus::PENDING,
            'company_name' => 'Delete Me Ltd',
        ]);

        Livewire::actingAs($admin)
            ->test(ApplicationsPage::class)
            ->call('deleteApplication', $application->id);

        $this->assertDatabaseMissing('distributor_requests', ['id' => $application->id]);
    }

    public function test_admin_can_purge_partner_from_public_and_portal(): void
    {
        $admin = $this->admin();
        $portalUser = User::factory()->create([
            'email' => 'partner-purge@example.com',
            'is_admin' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $distributor = Distributor::factory()->create([
            'user_id' => $portalUser->id,
            'status' => DistributorAccountStatus::ACTIVE,
            'company_name' => 'Purge Target Co',
            'district' => 'Kampala',
        ]);

        app(DistributorCoverageSync::class)->sync($distributor);

        Livewire::actingAs($admin)
            ->test(ActivePartnersPage::class)
            ->call('deletePartner', $distributor->id);

        $this->assertDatabaseMissing('distributors', ['id' => $distributor->id]);
        $this->assertDatabaseMissing('users', ['id' => $portalUser->id]);

        $this->getJson('/api/v1/public/distributors')
            ->assertSuccessful()
            ->assertJsonMissing(['company_name' => 'Purge Target Co']);
    }
}
