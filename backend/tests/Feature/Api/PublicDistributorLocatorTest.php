<?php

namespace Tests\Feature\Api;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Models\Distributor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDistributorLocatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_returns_locator_fields_and_filters(): void
    {
        Distributor::factory()->create([
            'company_name' => 'Gold Kampala Supplies',
            'trading_name' => 'Gold Kampala',
            'status' => DistributorAccountStatus::ACTIVE,
            'tier' => DistributorTier::GOLD,
            'district' => 'Kampala',
            'city' => 'Nakawa',
            'whatsapp' => '+256700000001',
            'google_maps_url' => 'https://maps.google.com/?q=kampala',
            'stock_availability' => DistributorStockAvailability::IN_STOCK,
        ]);

        Distributor::factory()->create([
            'company_name' => 'Silver Gulu Depot',
            'status' => DistributorAccountStatus::ACTIVE,
            'tier' => DistributorTier::SILVER,
            'district' => 'Gulu',
            'city' => 'Pece',
            'stock_availability' => DistributorStockAvailability::LOW_STOCK,
        ]);

        Distributor::factory()->suspended()->create([
            'company_name' => 'Hidden Suspended Co',
            'tier' => DistributorTier::MASTER,
            'district' => 'Kampala',
        ]);

        $this->getJson('/api/v1/public/distributors')
            ->assertSuccessful()
            ->assertJsonFragment([
                'company_name' => 'Gold Kampala Supplies',
                'tier' => 'gold',
                'tier_label' => 'Gold Distributor',
                'city' => 'Nakawa',
                'area' => 'Nakawa',
                'whatsapp' => '+256700000001',
                'google_maps_url' => 'https://maps.google.com/?q=kampala',
                'stock_availability' => 'in_stock',
                'stock_availability_label' => 'In Stock',
            ])
            ->assertJsonMissing(['company_name' => 'Hidden Suspended Co']);

        $this->getJson('/api/v1/public/distributors?tier=gold')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['company_name' => 'Gold Kampala Supplies']);

        $this->getJson('/api/v1/public/distributors?area=Nakawa')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['company_name' => 'Gold Kampala Supplies']);

        $this->getJson('/api/v1/public/distributors?stock_availability=low_stock')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['company_name' => 'Silver Gulu Depot']);

        $this->getJson('/api/v1/public/distributors?district=Kampala&tier=silver')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_public_index_rejects_invalid_tier_and_stock_filters(): void
    {
        $this->getJson('/api/v1/public/distributors?tier=platinum')
            ->assertStatus(422);

        $this->getJson('/api/v1/public/distributors?stock_availability=plenty')
            ->assertStatus(422);
    }
}
