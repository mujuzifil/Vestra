<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\SearchAnalytic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_autocomplete_returns_matching_products(): void
    {
        Product::factory()->create(['name' => 'Heavy Duty Detergent', 'status' => 'active']);
        Product::factory()->create(['name' => 'Silk Care Solution', 'status' => 'active']);

        $response = $this->getJson('/api/v1/search/autocomplete?q=heavy');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_search_records_analytics(): void
    {
        Product::factory()->create(['name' => 'EcoSuit Cleaner', 'status' => 'active']);

        $this->getJson('/api/v1/products?search=ecosuit');

        $this->assertDatabaseHas('search_analytics', [
            'term' => 'ecosuit',
        ]);
    }

    public function test_products_endpoint_supports_price_filter(): void
    {
        Product::factory()->create(['price' => 5000, 'status' => 'active']);
        Product::factory()->create(['price' => 15000, 'status' => 'active']);

        $response = $this->getJson('/api/v1/products?min_price=10000');

        $response->assertOk()
            ->assertJsonPath('data.meta.total', 1);
    }
}
