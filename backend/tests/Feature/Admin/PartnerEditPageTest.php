<?php

namespace Tests\Feature\Admin;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Filament\Pages\Distributors\PartnerEditPage;
use App\Models\Distributor;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerEditPageTest extends TestCase
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

    public function test_partner_edit_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.distributors.partners.edit'));
        $this->assertStringContainsString('/distributors/partners/edit', PartnerEditPage::getUrl(['partner' => 1]));
    }

    public function test_partner_edit_url_is_used_for_active_partners_edit_links(): void
    {
        $distributor = Distributor::factory()->create();

        $url = PartnerEditPage::getUrl(['partner' => $distributor->id]);

        $this->assertStringContainsString('/distributors/partners/edit', $url);
        $this->assertStringContainsString('partner='.$distributor->id, $url);
    }

    public function test_admin_can_update_tier_stock_and_locator_fields(): void
    {
        $admin = $this->admin();
        $distributor = Distributor::factory()->create([
            'company_name' => 'Editable Locator Co',
            'status' => DistributorAccountStatus::ACTIVE,
            'tier' => DistributorTier::SILVER,
            'stock_availability' => DistributorStockAvailability::IN_STOCK,
        ]);

        Livewire::actingAs($admin)
            ->test(PartnerEditPage::class, ['partner' => $distributor->id])
            ->set('form.company_name', 'Editable Locator Co')
            ->set('form.tier', DistributorTier::GOLD->value)
            ->set('form.stock_availability', DistributorStockAvailability::LOW_STOCK->value)
            ->set('form.whatsapp', '+256700111222')
            ->set('form.google_maps_url', 'https://maps.google.com/?q=kampala')
            ->set('form.district', 'Kampala')
            ->set('form.city', 'Nakawa')
            ->set('form.status', DistributorAccountStatus::ACTIVE->value)
            ->set('hourRows', [
                ['day' => 'Mon-Fri', 'hours' => '09:00-18:00'],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $distributor->refresh();

        $this->assertSame(DistributorTier::GOLD, $distributor->tier);
        $this->assertSame(DistributorStockAvailability::LOW_STOCK, $distributor->stock_availability);
        $this->assertSame('+256700111222', $distributor->whatsapp);
        $this->assertSame('https://maps.google.com/?q=kampala', $distributor->google_maps_url);
        $this->assertSame('Kampala', $distributor->district);
        $this->assertSame('Nakawa', $distributor->city);
        $this->assertSame(['Mon-Fri' => '09:00-18:00'], $distributor->operating_hours_json);
    }
}
