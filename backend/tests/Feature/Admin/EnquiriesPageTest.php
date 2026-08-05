<?php

namespace Tests\Feature\Admin;

use App\Enums\ContactEnquiryType;
use App\Enums\ContactStatus;
use App\Enums\Priority;
use App\Filament\Pages\CustomerSuccess\EnquiriesPage;
use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\Admin\EnquiryAdminService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class EnquiriesPageTest extends TestCase
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
            'is_admin'           => true,
            'email_verified_at'  => now(),
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'is_admin'           => false,
            'email_verified_at'  => now(),
        ]);
    }

    public function test_enquiries_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.pages.customer-success.enquiries'));
        $this->assertTrue(Route::has('filament.admin.customer-success.enquiries.export'));
        $this->assertStringContainsString('/customer-success/enquiries', EnquiriesPage::getUrl());
    }

    public function test_legacy_resource_is_hidden_from_navigation(): void
    {
        $this->assertFalse(ContactMessageResource::shouldRegisterNavigation());
        $this->assertSame([], ContactMessageResource::getNavigationItems());
    }

    public function test_legacy_list_page_redirects_to_enquiries_workspace(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/contact-messages')
            ->assertRedirect(EnquiriesPage::getUrl());
    }

    public function test_guest_is_redirected_from_enquiries_route(): void
    {
        $this->get('/customer-success/enquiries')->assertRedirect();
    }

    public function test_non_admin_is_denied_access(): void
    {
        $customer = $this->customer();

        Livewire::actingAs($customer)
            ->test(EnquiriesPage::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_enquiries_page_and_kpis(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->count(3)->create();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->assertSuccessful()
            ->assertSee('Enquiries')
            ->assertSee('Total')
            ->assertSee('New')
            ->assertSee('Resolved')
            ->assertDontSeeHtml('>Assigned To<');
    }

    public function test_empty_state_renders_when_no_enquiries_exist(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->assertSuccessful()
            ->assertSee('No enquiries yet');
    }

    public function test_search_filters_by_name_and_email(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->create([
            'name'    => 'Alice Johnson',
            'email'   => 'alice@example.com',
            'subject' => 'Product inquiry',
        ]);
        ContactMessage::factory()->create([
            'name'    => 'Bob Smith',
            'email'   => 'bob@example.com',
            'subject' => 'Support request',
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->set('search', 'alice@example.com')
            ->assertSee('Alice Johnson')
            ->assertDontSee('Bob Smith');
    }

    public function test_status_filter_works(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->create([
            'status' => ContactStatus::NEW,
            'name'   => 'New Customer',
        ]);
        ContactMessage::factory()->create([
            'status' => ContactStatus::RESOLVED,
            'name'   => 'Resolved Customer',
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->set('statusFilter', [ContactStatus::NEW->value])
            ->assertSee('New Customer')
            ->assertDontSee('Resolved Customer');
    }

    public function test_enquiry_type_filter_works(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->create([
            'enquiry_type' => ContactEnquiryType::SALES,
            'name'         => 'Sales Enquirer',
        ]);
        ContactMessage::factory()->create([
            'enquiry_type' => ContactEnquiryType::TECHNICAL_SUPPORT,
            'name'         => 'Tech Support Enquirer',
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->set('enquiryTypeFilter', [ContactEnquiryType::SALES->value])
            ->assertSee('Sales Enquirer')
            ->assertDontSee('Tech Support Enquirer');
    }

    public function test_priority_filter_works(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->create([
            'priority' => Priority::HIGH->value,
            'name'     => 'Urgent Customer',
        ]);
        ContactMessage::factory()->create([
            'priority' => Priority::LOW->value,
            'name'     => 'Low Priority Customer',
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->set('priorityFilter', [Priority::HIGH->value])
            ->assertSee('Urgent Customer')
            ->assertDontSee('Low Priority Customer');
    }

    public function test_kpi_cards_use_live_counts(): void
    {
        ContactMessage::factory()->count(2)->create(['status' => ContactStatus::NEW]);
        ContactMessage::factory()->create(['status' => ContactStatus::IN_PROGRESS]);
        ContactMessage::factory()->create(['status' => ContactStatus::RESOLVED]);

        $cards   = app(EnquiryAdminService::class)->getKpiCards();
        $byLabel = collect($cards)->keyBy('label');

        $this->assertEquals('4', $byLabel['Total']['value']);
        $this->assertEquals('2', $byLabel['New']['value']);
        $this->assertEquals('1', $byLabel['Resolved']['value']);
        $this->assertArrayNotHasKey('In Progress', $byLabel->all());
        $this->assertArrayNotHasKey('Unassigned', $byLabel->all());
    }

    public function test_detail_drawer_shows_live_enquiry_data(): void
    {
        $admin = $this->admin();

        $enquiry = ContactMessage::factory()->create([
            'name'    => 'Jane Drawer',
            'message' => 'Please help me with my order.',
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('openDetailDrawer', $enquiry->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedEnquiryId', $enquiry->id)
            ->assertSee('Jane Drawer')
            ->assertSee('Please help me with my order.');
    }

    public function test_opening_drawer_marks_enquiry_as_read(): void
    {
        $admin   = $this->admin();
        $enquiry = ContactMessage::factory()->create(['read_at' => null]);

        $this->assertNull($enquiry->fresh()->read_at);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('openDetailDrawer', $enquiry->id);

        $this->assertNotNull($enquiry->fresh()->read_at);
    }

    public function test_close_drawer_resets_state(): void
    {
        $admin   = $this->admin();
        $enquiry = ContactMessage::factory()->create();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('openDetailDrawer', $enquiry->id)
            ->assertSet('showDetailDrawer', true)
            ->call('closeDetailDrawer')
            ->assertSet('showDetailDrawer', false)
            ->assertSet('selectedEnquiryId', null);
    }

    public function test_admin_can_mark_enquiry_resolved(): void
    {
        $admin   = $this->admin();
        $enquiry = ContactMessage::factory()->create(['status' => ContactStatus::NEW]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('markResolved', $enquiry->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'id'     => $enquiry->id,
            'status' => ContactStatus::RESOLVED->value,
        ]);
    }

    public function test_admin_can_update_status(): void
    {
        $admin   = $this->admin();
        $enquiry = ContactMessage::factory()->create(['status' => ContactStatus::NEW]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('updateStatus', $enquiry->id, ContactStatus::IN_PROGRESS->value)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'id'     => $enquiry->id,
            'status' => ContactStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_send_reply_dispatches_mail_and_marks_replied(): void
    {
        Mail::fake();

        $admin   = $this->admin();
        $enquiry = ContactMessage::factory()->create([
            'email'      => 'customer@example.com',
            'replied_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('openDetailDrawer', $enquiry->id)
            ->set('replyDraft', 'Thank you for your enquiry.')
            ->call('sendReply')
            ->assertHasNoErrors();

        Mail::assertSent(\App\Mail\ContactReplyMail::class);

        $this->assertNotNull($enquiry->fresh()->replied_at);
        $this->assertEquals(ContactStatus::RESOLVED->value, $enquiry->fresh()->status->value);
    }

    public function test_export_returns_filtered_rows(): void
    {
        ContactMessage::factory()->create([
            'status' => ContactStatus::RESOLVED,
            'name'   => 'Resolved Person',
        ]);
        ContactMessage::factory()->create([
            'status' => ContactStatus::NEW,
            'name'   => 'New Person',
        ]);

        $rows = app(EnquiryAdminService::class)->exportRows([
            'status' => [ContactStatus::RESOLVED->value],
        ]);

        $this->assertCount(1, $rows);
        $this->assertEquals('Resolved Person', $rows[0]['name']);
    }

    public function test_export_route_requires_admin(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('filament.admin.customer-success.enquiries.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_route_downloads_csv_for_admin(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('filament.admin.customer-success.enquiries.export', ['format' => 'csv']));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $admin = $this->admin();

        ContactMessage::factory()->count(25)->create();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'zzz-no-match-xyz')
            ->assertSet('paginators.page', 1);
    }

    public function test_sort_by_changes_sort_field_and_direction(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->call('sortBy', 'name')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'name')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_reset_filters_clears_all_filter_state(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(EnquiriesPage::class)
            ->set('search', 'test')
            ->set('statusFilter', [ContactStatus::NEW->value])
            ->set('priorityFilter', [Priority::HIGH->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', [])
            ->assertSet('priorityFilter', []);
    }
}
