<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;

/**
 * Tests for the AGENT role:
 *  - Login / logout
 *  - View dashboard
 *  - Can view assigned ticket
 *  - Cannot view unassigned ticket (403)
 *  - Can add public comment and internal note
 *  - Can resolve an assigned ticket
 *  - Can escalate a ticket
 *  - Cannot close ticket (team_lead/admin only)
 *  - Cannot access admin panel (403)
 *  - Cannot delete ticket (admin only)
 */
class AgentFlowTest extends EtmsTestCase
{
    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_agent_can_login(): void
    {
        $agent = $this->createAgent();
        $this->post('/login', [
            'email'    => $agent->email,
            'password' => 'password',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($agent);
    }

    public function test_agent_can_logout(): void
    {
        $this->actingAs($this->createAgent())
             ->post('/logout')
             ->assertRedirect();
        $this->assertGuest();
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function test_agent_can_view_dashboard(): void
    {
        $this->actingAs($this->createAgent())
             ->get('/dashboard')
             ->assertStatus(200);
    }

    // ─── Ticket Access ────────────────────────────────────────────────────────

    public function test_agent_can_view_assigned_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, [
            'assigned_to' => $agent->id,
            'status'      => 'assigned',
        ]);

        $this->actingAs($agent)
             ->get("/tickets/{$ticket->id}")
             ->assertStatus(200)
             ->assertSee($ticket->title);
    }

    public function test_agent_cannot_view_unassigned_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer); // not assigned to agent

        $this->actingAs($agent)
             ->get("/tickets/{$ticket->id}")
             ->assertStatus(403);
    }

    // ─── Comment ──────────────────────────────────────────────────────────────

    public function test_agent_can_add_public_comment(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, ['assigned_to' => $agent->id]);

        $this->actingAs($agent)->post("/tickets/{$ticket->id}/comments", [
            'body' => 'Agent public reply on this ticket.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id'   => $ticket->id,
            'user_id'     => $agent->id,
            'is_internal' => false,
        ]);
    }

    public function test_agent_can_add_internal_note(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, ['assigned_to' => $agent->id]);

        $this->actingAs($agent)->post("/tickets/{$ticket->id}/comments", [
            'body'        => 'Internal note by agent for team.',
            'is_internal' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id'   => $ticket->id,
            'user_id'     => $agent->id,
            'is_internal' => true,
        ]);
    }

    // ─── Resolve ──────────────────────────────────────────────────────────────

    public function test_agent_can_resolve_assigned_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, [
            'assigned_to' => $agent->id,
            'status'      => 'in_progress',
        ]);

        $this->actingAs($agent)->post("/tickets/{$ticket->id}/resolve", [
            'resolution_note' => 'Issue has been resolved successfully by the agent team.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'resolved',
        ]);
    }

    // ─── Escalate ─────────────────────────────────────────────────────────────

    public function test_agent_can_escalate_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, ['assigned_to' => $agent->id]);

        $this->actingAs($agent)->post("/tickets/{$ticket->id}/escalate", [
            'reason' => 'This issue needs senior attention from team lead.',
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'escalated',
        ]);
    }

    // ─── Forbidden Actions ────────────────────────────────────────────────────

    public function test_agent_cannot_close_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer, [
            'assigned_to' => $agent->id,
            'status'      => 'resolved',
        ]);

        $this->actingAs($agent)
             ->post("/tickets/{$ticket->id}/close")
             ->assertStatus(403);
    }

    public function test_agent_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->createAgent())
             ->get('/admin/users')
             ->assertStatus(403);
    }

    public function test_agent_cannot_delete_ticket(): void
    {
        $customer = $this->createCustomer();
        $agent    = $this->createAgent();
        $ticket   = $this->createTicket($customer);

        $this->actingAs($agent)
             ->delete("/tickets/{$ticket->id}")
             ->assertStatus(403);
    }

    // ─── Ticket List ──────────────────────────────────────────────────────────

    public function test_agent_can_view_ticket_list(): void
    {
        $this->actingAs($this->createAgent())
             ->get('/tickets')
             ->assertStatus(200);
    }
}
