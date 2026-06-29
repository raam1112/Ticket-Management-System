<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\DB;

/**
 * AssignmentService — handles ticket assignment logic (manual + auto round-robin).
 */
class AssignmentService
{
    /**
     * Manually assign a ticket to a specific agent.
     */
    public function assignTo(Ticket $ticket, User $agent, ?User $assignedBy = null, ?string $note = null): void
    {
        DB::transaction(function () use ($ticket, $agent, $assignedBy, $note) {
            $oldAgent = $ticket->assigned_to;

            $ticket->update([
                'assigned_to' => $agent->id,
                'assigned_by' => $assignedBy?->id ?? auth()->id(),
                'status'      => 'assigned',
            ]);

            TicketAssignment::create([
                'ticket_id'   => $ticket->id,
                'assigned_to' => $agent->id,
                'assigned_by' => $assignedBy?->id ?? auth()->id(),
                'action'      => $oldAgent ? 'transferred' : 'assigned',
                'reason'      => $note,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => auth()->id(),
                'action'    => $oldAgent ? 'reassigned' : 'assigned',
                'old_value' => $oldAgent ? User::find($oldAgent)?->name : null,
                'new_value' => $agent->name,
                'note'      => $note,
            ]);

            // Notify Agent
            $agent->notify(new TicketNotification($ticket, 'Ticket Assigned', "Ticket #{$ticket->reference_number} has been assigned to you."));
        });
    }

    /**
     * Auto-assign a ticket using round-robin among available agents.
     * Selects the agent with the fewest open assigned tickets.
     */
    public function autoAssign(Ticket $ticket): ?User
    {
        $agent = User::byRole('agent')
            ->active()
            ->where('availability_status', 'available')
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereNotIn('status', ['resolved', 'closed', 'cancelled']);
            }])
            ->havingRaw('assigned_tickets_count < users.agent_capacity')
            ->orderBy('assigned_tickets_count')
            ->first();

        if ($agent) {
            $this->assignTo($ticket, $agent, null, 'Auto-assigned (round-robin)');
            
            if (($agent->assigned_tickets_count + 1) >= $agent->agent_capacity) {
                $agent->update(['availability_status' => 'busy']);
                \App\Models\AgentStatusHistory::create([
                    'user_id' => $agent->id,
                    'status'  => 'busy',
                    'reason'  => 'Auto-away (Reached max capacity)',
                ]);
            }
        }

        return $agent;
    }

    /**
     * Agent accepts their assignment.
     */
    public function accept(Ticket $ticket, User $agent): void
    {
        DB::transaction(function () use ($ticket, $agent) {
            $ticket->update(['status' => 'in_progress']);

            TicketAssignment::create([
                'ticket_id'   => $ticket->id,
                'assigned_to' => $agent->id,
                'assigned_by' => $agent->id,
                'action'      => 'accepted',
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => $agent->id,
                'action'    => 'status_changed',
                'old_value' => 'assigned',
                'new_value' => 'in_progress',
                'note'      => 'Agent accepted the ticket',
            ]);
        });
    }

    /**
     * Agent rejects their assignment — ticket goes back to open.
     */
    public function reject(Ticket $ticket, User $agent, string $reason): void
    {
        DB::transaction(function () use ($ticket, $agent, $reason) {
            $ticket->update([
                'assigned_to' => null,
                'status'      => 'open',
            ]);

            TicketAssignment::create([
                'ticket_id'   => $ticket->id,
                'assigned_to' => $agent->id,
                'assigned_by' => $agent->id,
                'action'      => 'rejected',
                'reason'      => $reason,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => $agent->id,
                'action'    => 'status_changed',
                'old_value' => 'assigned',
                'new_value' => 'open',
                'note'      => "Rejected: {$reason}",
            ]);
        });
    }
}
