<?php

namespace Tests\Feature\Api\V1\Account;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportTicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_support_ticket(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/account/support', [
            'subject' => 'Need help',
            'message' => 'I need assistance with my quote.',
        ]);

        $response->assertCreated()->assertJsonPath('data.subject', 'Need help');
        $this->assertDatabaseHas('support_tickets', ['user_id' => $user->id, 'subject' => 'Need help']);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'support_ticket_created',
        ]);
    }

    public function test_customer_can_reply_to_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/account/support/{$ticket->id}/reply", [
            'message' => 'Any update on this?',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'Any update on this?',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'support_reply_created',
        ]);
    }

    public function test_customer_cannot_view_other_customer_ticket(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/account/support/{$ticket->id}");
        $response->assertForbidden();
    }
}
