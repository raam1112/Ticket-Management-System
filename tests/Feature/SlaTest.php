<?php

namespace Tests\Feature;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\Ticket;
use App\Models\SlaPolicy;
use App\Services\SlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sla_policy_calculates_correct_due_date(): void
    {
        $priority = TicketPriority::factory()->create(['sla_hours_resolve' => 24]);

        $ticket = Ticket::factory()->create([
            'priority_id' => $priority->id,
            'status'      => 'open',
        ]);

        $slaService = new SlaService();
        $slaService->applySlaPolicyToTicket($ticket);

        $this->assertNotNull($ticket->sla_resolve_at);
        $this->assertTrue($ticket->sla_resolve_at->isFuture());
    }

    public function test_sla_policy_override_by_custom_policy(): void
    {
        $category = TicketCategory::factory()->create();
        $priority = TicketPriority::factory()->create(['sla_hours_resolve' => 48]);

        $policy = SlaPolicy::create([
            'name'                   => 'High Priority Network Issue',
            'category_id'            => $category->id,
            'priority_id'            => $priority->id,
            'response_time_hours'    => 1,
            'resolution_time_hours'  => 4,
            'escalation_after_hours' => 2,
            'is_active'              => true,
        ]);

        $ticket = Ticket::factory()->create([
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'status'      => 'open',
        ]);

        $slaService = new SlaService();
        $slaService->applySlaPolicyToTicket($ticket);

        // It should use the custom policy (4 hours) instead of the priority default (48 hours)
        $expectedTime = now()->addHours(4);
        $this->assertEquals(
            $expectedTime->format('Y-m-d H'),
            $ticket->sla_resolve_at->format('Y-m-d H')
        );
    }
}
