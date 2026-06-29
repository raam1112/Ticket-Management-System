<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;

/**
 * Tests for the ADMIN role:
 *  - Login / logout
 *  - Dashboard access
 *  - Can view ANY ticket
 *  - Can assign ticket to agent
 *  - Can resolve ticket
 *  - Can close ticket
 *  - Can delete ticket
 *  - Can access admin panel (users, categories, priorities, departments)
 *  - Can create/update SLA policies
 *  - Can view audit logs
 *  - Can view reports
 *  - Can access knowledge base management
 */
class AdminFlowTest extends EtmsTestCase
{
    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_admin_can_login(): void
    {
        $admin = $this->createAdmin();
        $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->createAdmin())
             ->post('/logout')
             ->assertRedirect();
        $this->assertGuest();
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/dashboard')
             ->assertStatus(200);
    }

    // ─── Ticket Access ────────────────────────────────────────────────────────

    public function test_admin_can_view_any_ticket(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $ticket   = $this->createTicket($customer, ['title' => 'Admin Viewable Ticket']);

        $this->actingAs($admin)
             ->get("/tickets/{$ticket->id}")
             ->assertStatus(200)
             ->assertSee('Admin Viewable Ticket');
    }

    public function test_admin_can_view_all_tickets(): void
    {
        $admin = $this->createAdmin();
        $this->createTicket($this->createCustomer());
        $this->createTicket($this->createCustomer());

        $this->actingAs($admin)
             ->get('/tickets')
             ->assertStatus(200);
    }

    // ─── Assign Ticket ────────────────────────────────────────────────────────

    public function test_admin_can_assign_ticket_to_agent(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer);

        $this->actingAs($admin)->post("/tickets/{$ticket->id}/assign", [
            'agent_id' => $agent->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'          => $ticket->id,
            'assigned_to' => $agent->id,
        ]);
    }

    // ─── Resolve & Close ──────────────────────────────────────────────────────

    public function test_admin_can_resolve_any_ticket(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $ticket   = $this->createTicket($customer, ['status' => 'in_progress']);

        $this->actingAs($admin)->post("/tickets/{$ticket->id}/resolve", [
            'resolution_note' => 'Admin resolved this ticket directly for the user.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    public function test_admin_can_close_ticket(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $ticket   = $this->createTicket($customer, ['status' => 'resolved']);

        $this->actingAs($admin)
             ->post("/tickets/{$ticket->id}/close")
             ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'closed',
        ]);
    }

    // ─── Delete Ticket ────────────────────────────────────────────────────────

    public function test_admin_can_delete_ticket(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $ticket   = $this->createTicket($customer);

        $this->actingAs($admin)
             ->delete("/tickets/{$ticket->id}")
             ->assertRedirect(route('tickets.index'));

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
    }

    // ─── Admin Panel: Users ───────────────────────────────────────────────────

    public function test_admin_can_access_user_management(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/users')
             ->assertStatus(200);
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/users/create')
             ->assertStatus(200);
    }

    // ─── Admin Panel: Categories ──────────────────────────────────────────────

    public function test_admin_can_list_categories(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/categories')
             ->assertStatus(200);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin)->post('/admin/categories', [
            'name'        => 'New Test Category',
            'slug'        => 'new-test-category',
            'description' => 'A category for testing purposes',
            'is_active'   => '1',
            'sort_order'  => '10',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_categories', ['slug' => 'new-test-category']);
    }

    // ─── Admin Panel: Priorities ──────────────────────────────────────────────

    public function test_admin_can_list_priorities(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/priorities')
             ->assertStatus(200);
    }

    public function test_admin_can_create_priority(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin)->post('/admin/priorities', [
            'name'               => 'critical',
            'display_name'       => 'Critical',
            'color'              => '#dc2626',
            'icon'               => 'fa-fire',
            'sla_hours_response' => 1,
            'sla_hours_resolve'  => 4,
            'sort_order'         => 1,
            'is_active'          => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_priorities', ['name' => 'critical']);
    }

    // ─── Admin Panel: SLA Policies ────────────────────────────────────────────

    public function test_admin_can_list_sla_policies(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/sla-policies')
             ->assertStatus(200);
    }

    public function test_admin_can_create_sla_policy(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin)->post('/admin/sla-policies', [
            'name'                   => 'Standard SLA',
            'response_time_hours'    => 2,
            'resolution_time_hours'  => 8,
            'escalation_after_hours' => 4,
            'is_active'              => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('sla_policies', ['name' => 'Standard SLA']);
    }

    // ─── Admin Panel: Departments ─────────────────────────────────────────────

    public function test_admin_can_list_departments(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/departments')
             ->assertStatus(200);
    }

    // ─── Audit Logs ───────────────────────────────────────────────────────────

    public function test_admin_can_view_audit_logs(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/audit-logs')
             ->assertStatus(200);
    }

    // ─── Reports ──────────────────────────────────────────────────────────────

    public function test_admin_can_view_reports_page(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/reports')
             ->assertStatus(200);
    }

    // ─── Knowledge Base ───────────────────────────────────────────────────────

    public function test_admin_can_view_kb_articles(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/kb-articles')
             ->assertStatus(200);
    }

    public function test_admin_can_view_sla_monitor(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/sla-monitor')
             ->assertStatus(200);
    }

    // ─── Settings ────────────────────────────────────────────────────────────

    public function test_admin_can_view_system_settings(): void
    {
        $this->actingAs($this->createAdmin())
             ->get('/admin/settings')
             ->assertStatus(200);
    }

    // ─── Escalate ─────────────────────────────────────────────────────────────

    public function test_admin_can_escalate_ticket(): void
    {
        $admin    = $this->createAdmin();
        $customer = $this->createCustomer();
        $ticket   = $this->createTicket($customer, ['status' => 'open']);

        $this->actingAs($admin)->post("/tickets/{$ticket->id}/escalate", [
            'reason' => 'Escalating because this is a critical production issue.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'escalated',
        ]);
    }

    // ─── Non-admin blocked ────────────────────────────────────────────────────

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->createCustomer())
             ->get('/admin/users')
             ->assertStatus(403);
    }

    public function test_agent_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->createAgent())
             ->get('/admin/users')
             ->assertStatus(403);
    }
}
