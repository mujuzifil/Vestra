<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\CustomerSuccess\SupportPage;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class SupportPageTest extends TestCase
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
            'email_verified_at' => now(),
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    private function makeTicket(User $user, array $overrides = []): SupportTicket
    {
        return SupportTicket::factory()->create(array_merge([
            'user_id' => $user->id,
            'reference_number' => 'ST-'.now()->format('Ymd').'-0001',
            'subject' => 'Test Support Request',
            'enquiry_type' => 'general',
            'message' => 'Please help me.',
            'status' => 'open',
            'priority' => 'medium',
        ], $overrides));
    }

    public function test_support_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.customer-success.support'));
        $this->assertTrue(Route::has('filament.admin.customer-success.support.export'));
        $this->assertStringContainsString('/customer-success/support', SupportPage::getUrl());
    }

    public function test_guest_is_redirected_from_support_route(): void
    {
        $this->get('/customer-success/support')->assertRedirect();
    }

    public function test_non_admin_is_denied_access_to_support_page(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(SupportPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_support_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->assertSuccessful()
            ->assertSee('Support');
    }

    public function test_kpi_cards_shown_to_admin(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $this->makeTicket($customer, ['status' => 'open']);
        $this->makeTicket($customer, ['status' => 'in_progress']);
        $this->makeTicket($customer, ['status' => 'resolved']);
        $this->makeTicket($customer, ['status' => 'closed']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->assertSuccessful()
            ->assertSee('Total')
            ->assertSee('Open')
            ->assertSee('In Progress')
            ->assertSee('Resolved')
            ->assertSee('Closed');
    }

    public function test_empty_state_renders_when_no_tickets(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->assertSuccessful()
            ->assertSee('No support tickets yet');
    }

    public function test_tickets_appear_in_table(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $this->makeTicket($customer, ['subject' => 'Unique Subject Inquiry ABC']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->assertSuccessful()
            ->assertSee('Unique Subject Inquiry ABC');
    }

    public function test_search_filters_tickets(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $this->makeTicket($customer, ['subject' => 'Alpha Ticket']);
        $this->makeTicket($customer, ['subject' => 'Beta Inquiry']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Ticket')
            ->assertDontSee('Beta Inquiry');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $this->makeTicket($customer, ['subject' => 'Open Ticket', 'status' => 'open']);
        $this->makeTicket($customer, ['subject' => 'Closed Ticket', 'status' => 'closed']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->set('statusFilter', ['open'])
            ->assertSee('Open Ticket')
            ->assertDontSee('Closed Ticket');
    }

    public function test_priority_filter_works(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $this->makeTicket($customer, ['subject' => 'Urgent Issue', 'priority' => 'urgent']);
        $this->makeTicket($customer, ['subject' => 'Low Priority', 'priority' => 'low']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->set('priorityFilter', ['urgent'])
            ->assertSee('Urgent Issue')
            ->assertDontSee('Low Priority');
    }

    public function test_admin_can_open_detail_drawer(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedTicketId', $ticket->id);
    }

    public function test_admin_can_close_detail_drawer(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedTicketId', null);
    }

    public function test_admin_can_update_ticket_status(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer, ['status' => 'open']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->set('updateStatus', 'in_progress')
            ->call('updateTicketStatus', $ticket->id);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_resolved_at_set_when_status_changed_to_resolved(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer, ['status' => 'open']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->set('updateStatus', 'resolved')
            ->call('updateTicketStatus', $ticket->id);

        $this->assertNotNull(SupportTicket::find($ticket->id)->resolved_at);
    }

    public function test_admin_can_submit_reply(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer, ['status' => 'open']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->set('replyMessage', 'Thank you for contacting us.')
            ->set('replyIsInternal', false)
            ->call('submitReply');

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'staff_id' => $admin->id,
            'message' => 'Thank you for contacting us.',
            'is_internal' => false,
        ]);
    }

    public function test_admin_can_submit_internal_note(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $ticket = $this->makeTicket($customer, ['status' => 'open']);

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('openDetailDrawer', $ticket->id)
            ->set('replyMessage', 'Internal observation.')
            ->set('replyIsInternal', true)
            ->call('submitReply');

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'staff_id' => $admin->id,
            'message' => 'Internal observation.',
            'is_internal' => true,
        ]);
    }

    public function test_sort_by_toggles_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->call('sortBy', 'subject')
            ->assertSet('sortField', 'subject')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'subject')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SupportPage::class)
            ->set('search', 'something')
            ->set('statusFilter', ['open'])
            ->set('priorityFilter', ['urgent'])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', [])
            ->assertSet('priorityFilter', []);
    }

    public function test_export_url_built_correctly(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(SupportPage::class);
        $url = $component->instance()->getExportUrl('csv');

        $this->assertStringContainsString('customer-success/support/export', $url);
        $this->assertStringContainsString('format=csv', $url);
    }

    public function test_navigation_sort_is_one(): void
    {
        $this->assertSame(1, SupportPage::getNavigationSort());
    }
}
