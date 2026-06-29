<?php

namespace Tests\Feature;

use App\Models\Ticket;

/**
 * Tests for the CUSTOMER (user) role:
 *  - Login / logout
 *  - View own tickets
 *  - Create ticket (valid + validation errors)
 *  - View own ticket detail
 *  - Add comment to own open ticket
 *  - Cannot view another user's ticket (403)
 *  - Cannot access admin area (403)
 *  - Cannot resolve/close ticket (403)
 */
class CustomerFlowTest extends EtmsTestCase
{
    // ─── Auth ─────────────────────────────────────────────────────────────────

    public function test_unauthenticated_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/tickets')->assertRedirect('/login');
    }

    public function test_customer_can_login(): void
    {
        $user = $this->createCustomer();
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_login_fails_with_wrong_password(): void
    {
        $user = $this->createCustomer();
        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_customer_can_logout(): void
    {
        $user = $this->createCustomer();
        $this->actingAs($user)->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function test_customer_can_see_dashboard(): void
    {
        $this->actingAs($this->createCustomer())
             ->get('/dashboard')
             ->assertStatus(200);
    }

    // ─── Ticket List ──────────────────────────────────────────────────────────

    public function test_customer_can_view_ticket_list(): void
    {
        $user = $this->createCustomer();
        $this->createTicket($user);

        $this->actingAs($user)
             ->get('/tickets')
             ->assertStatus(200)
             ->assertSee('Sample Test Ticket Title');
    }

    public function test_customer_cannot_see_other_users_tickets(): void
    {
        $user1  = $this->createCustomer();
        $user2  = $this->createCustomer();
        $ticket = $this->createTicket($user2);

        // List should not show another user's ticket
        $this->actingAs($user1)
             ->get('/tickets')
             ->assertStatus(200)
             ->assertDontSee($ticket->title);
    }

    // ─── Create Ticket ────────────────────────────────────────────────────────

    public function test_customer_can_open_create_ticket_form(): void
    {
        $this->actingAs($this->createCustomer())
             ->get('/tickets/create')
             ->assertStatus(200);
    }

    public function test_customer_can_submit_valid_ticket(): void
    {
        $user     = $this->createCustomer();
        $category = \App\Models\TicketCategory::first();
        $priority = \App\Models\TicketPriority::first();

        $response = $this->actingAs($user)->post('/tickets', [
            'title'       => 'This is a valid ticket title',
            'description' => 'This is a detailed description of the issue that is long enough.',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'created_by' => $user->id,
            'title'      => 'This is a valid ticket title',
        ]);
    }

    public function test_create_ticket_fails_with_short_title(): void
    {
        $user     = $this->createCustomer();
        $category = \App\Models\TicketCategory::first();
        $priority = \App\Models\TicketPriority::first();

        $this->actingAs($user)->post('/tickets', [
            'title'       => 'Short',    // < 10 chars — must fail
            'description' => 'Some long enough description that meets the minimum.',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ])->assertSessionHasErrors(['title']);
    }

    public function test_create_ticket_fails_with_missing_fields(): void
    {
        $this->actingAs($this->createCustomer())
             ->post('/tickets', [])
             ->assertSessionHasErrors(['title', 'description', 'category_id', 'priority_id']);
    }

    // ─── View Own Ticket ──────────────────────────────────────────────────────

    public function test_customer_can_view_own_ticket_detail(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user, ['title' => 'My Detail Ticket']);

        $this->actingAs($user)
             ->get("/tickets/{$ticket->id}")
             ->assertStatus(200)
             ->assertSee('My Detail Ticket');
    }

    public function test_customer_cannot_view_another_users_ticket(): void
    {
        $user1  = $this->createCustomer();
        $user2  = $this->createCustomer();
        $ticket = $this->createTicket($user2);

        $this->actingAs($user1)
             ->get("/tickets/{$ticket->id}")
             ->assertStatus(403);
    }

    // ─── Add Comment ──────────────────────────────────────────────────────────

    public function test_customer_can_add_comment_to_own_ticket(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user);

        $this->actingAs($user)->post("/tickets/{$ticket->id}/comments", [
            'body' => 'This is my reply on the ticket.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
        ]);
    }

    public function test_customer_cannot_post_internal_note(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user);

        $this->actingAs($user)->post("/tickets/{$ticket->id}/comments", [
            'body'        => 'Trying to post internal note.',
            'is_internal' => '1',
        ])->assertRedirect();

        // Should have been saved as public (is_internal = 0)
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id'   => $ticket->id,
            'is_internal' => false,
        ]);
    }

    // ─── Forbidden Actions ────────────────────────────────────────────────────

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->createCustomer())
             ->get('/admin/users')
             ->assertStatus(403);
    }

    public function test_customer_cannot_resolve_ticket(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user);

        $this->actingAs($user)->post("/tickets/{$ticket->id}/resolve", [
            'resolution_note' => 'Trying to resolve as customer user.',
        ])->assertStatus(403);
    }

    public function test_customer_cannot_close_ticket(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user);

        $this->actingAs($user)
             ->post("/tickets/{$ticket->id}/close")
             ->assertStatus(403);
    }

    public function test_customer_cannot_escalate_ticket(): void
    {
        $user   = $this->createCustomer();
        $ticket = $this->createTicket($user);

        $this->actingAs($user)->post("/tickets/{$ticket->id}/escalate", [
            'reason' => 'Trying to escalate as customer.',
        ])->assertStatus(403);
    }
}
