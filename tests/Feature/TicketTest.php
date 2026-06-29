<?php

namespace Tests\Feature;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket(): void
    {
        // Create requirements
        $user     = User::factory()->create();
        $category = TicketCategory::factory()->create();
        $priority = TicketPriority::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'title'       => 'Test Ticket Issue',
            'description' => 'This is a test issue description.',
            'category_id' => $category->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('tickets', [
            'title'      => 'Test Ticket Issue',
            'created_by' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_view_own_tickets(): void
    {
        $user   = User::factory()->create();
        // Use a short title so it is never truncated in the ticket list view
        $ticket = Ticket::factory()->create(['created_by' => $user->id, 'title' => 'My Short Test Ticket']);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertStatus(200);
        $response->assertSee('My Short Test Ticket');
    }
}
