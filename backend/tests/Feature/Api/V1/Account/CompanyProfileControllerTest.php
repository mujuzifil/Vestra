<?php

namespace Tests\Feature\Api\V1\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_company_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/account/company');
        $response->assertOk()->assertJsonPath('data.primary_contact_email', $user->email);
    }

    public function test_customer_can_update_company_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/account/company', [
            'company_name' => 'Acme Ltd',
            'industry' => 'Hospitality',
            'city' => 'Kampala',
        ]);

        $response->assertOk()->assertJsonPath('data.company_name', 'Acme Ltd');
        $this->assertDatabaseHas('company_profiles', ['user_id' => $user->id, 'company_name' => 'Acme Ltd']);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'company_profile_updated',
        ]);
    }

    public function test_customer_cannot_escalate_company_status_via_api(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/account/company', [
            'company_name' => 'Acme Ltd',
            'status' => 'active',
            'account_manager_id' => 9999,
        ])->assertOk();

        $this->assertDatabaseHas('company_profiles', [
            'user_id' => $user->id,
            'company_name' => 'Acme Ltd',
            'status' => 'prospect',
        ]);
    }
}
