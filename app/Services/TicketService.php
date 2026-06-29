<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketEscalation;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\DB;

/**
 * TicketService — core ticket business logic.
 */
class TicketService
{
    public function __construct(
        protected SlaService $slaService,
        protected AssignmentService $assignmentService
    ) {}

    /**
     * Create a new ticket and set SLA deadlines.
     */
    public function create(array $data, User $creator): Ticket
    {
        return DB::transaction(function () use ($data, $creator) {
            $ticket = Ticket::create([
                'title'         => $data['title'],
                'description'   => $data['description'],
                'category_id'   => $data['category_id'],
                'priority_id'   => $data['priority_id'],
                'created_by'    => $creator->id,
                'department_id' => $data['department_id'] ?? $creator->department_id,
                'tags'          => $data['tags'] ?? null,
                'source'        => $data['source'] ?? 'web',
            ]);

            // Apply SLA policy
            $this->slaService->applySlaPolicyToTicket($ticket);
            $ticket->save();

            // Record creation history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => $creator->id,
                'action'    => 'created',
                'new_value' => $ticket->reference_number,
            ]);

            // Auto-assign if mode is set
            $mode = config('etms.tickets.assignment_mode', 'manual');
            if ($mode === 'auto') {
                $this->assignmentService->autoAssign($ticket);
            }

            // Audit log
            AuditLog::record('created', $creator->id, Ticket::class, $ticket->id);

            // Notify Creator
            $creator->notify(new TicketNotification($ticket, 'Ticket Created', 'Your ticket has been created successfully.'));

            // Notify Team Leads and Admins
            $teamLeadsAndAdmins = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['team_lead', 'admin']);
            })->active()->get();
            
            foreach ($teamLeadsAndAdmins as $manager) {
                $manager->notify(new TicketNotification($ticket, 'New Ticket', "A new ticket (#{$ticket->reference_number}) was created by {$creator->name}."));
            }

            return $ticket;
        });
    }

    /**
     * Change ticket status with validation of allowed transitions.
     */
    public function changeStatus(Ticket $ticket, string $newStatus, ?User $actor = null, ?string $note = null): bool
    {
        $actor = $actor ?? auth()->user();
        $allowedTransitions = config('etms.status_transitions', []);
        $allowed = $allowedTransitions[$ticket->status] ?? [];

        if (!in_array($newStatus, $allowed)) {
            return false;
        }

        $oldStatus = $ticket->status;

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => $actor->id,
            'action'    => 'status_changed',
            'field'     => 'status',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'note'      => $note,
        ]);

        $ticket->status = $newStatus;

        // Set timestamps based on new status
        match ($newStatus) {
            'resolved' => $ticket->resolved_at = now(),
            'closed'   => $ticket->closed_at = now(),
            'reopened' => [$ticket->reopened_at = now(), $ticket->reopen_count++],
            default    => null,
        };

        // Track SLA on resolution
        if ($newStatus === 'resolved') {
            $this->slaService->recordResolution($ticket);
        }

        $ticket->save();
        AuditLog::record('status_changed', $actor->id, Ticket::class, $ticket->id);

        // Notify creator if someone else changes status
        if ($ticket->creator && $ticket->creator->id !== $actor->id) {
            $ticket->creator->notify(new TicketNotification($ticket, 'Status Updated', "The status of your ticket is now {$newStatus}."));
        }

        return true;
    }

    /**
     * Escalate a ticket.
     */
    public function escalate(Ticket $ticket, User $escalatedBy, string $reason, ?User $escalatedTo = null): void
    {
        DB::transaction(function () use ($ticket, $escalatedBy, $reason, $escalatedTo) {
            $ticket->update([
                'status'           => 'escalated',
                'escalation_count' => $ticket->escalation_count + 1,
            ]);

            TicketEscalation::create([
                'ticket_id'       => $ticket->id,
                'escalated_by'    => $escalatedBy->id,
                'escalated_to'    => $escalatedTo?->id,
                'reason'          => $reason,
                'escalation_type' => 'manual',
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => $escalatedBy->id,
                'action'    => 'escalated',
                'note'      => $reason,
            ]);

            AuditLog::record('escalated', $escalatedBy->id, Ticket::class, $ticket->id);

            // Notify team leads or escalated_to user
            if ($escalatedTo) {
                $escalatedTo->notify(new TicketNotification($ticket, 'Ticket Escalated', "Ticket #{$ticket->reference_number} has been escalated to you."));
            } else {
                $teamLeads = User::byRole('team_lead')->get();
                foreach ($teamLeads as $lead) {
                    $lead->notify(new TicketNotification($ticket, 'Ticket Escalated', "Ticket #{$ticket->reference_number} has been escalated."));
                }
            }
        });
    }

    /**
     * Resolve a ticket.
     */
    public function resolve(Ticket $ticket, User $resolver, string $resolutionNote, string $level = 'standard'): void
    {
        DB::transaction(function () use ($ticket, $resolver, $resolutionNote, $level) {
            $this->slaService->recordResolution($ticket);

            $ticket->update([
                'status'           => 'resolved',
                'resolution_note'  => $resolutionNote,
                'resolution_level' => $level,
                'resolved_at'      => now(),
                'sla_resolve_met'  => $ticket->sla_resolve_met,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id'  => $resolver->id,
                'action'    => 'resolved',
                'note'      => $resolutionNote,
            ]);

            // Notify creator, team lead, and admins
            if ($ticket->creator) {
                $ticket->creator->notify(new TicketNotification($ticket, 'Ticket Resolved', "Your ticket has been resolved."));
            }
            $teamLeadsAndAdmins = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['team_lead', 'admin']);
            })->active()->get();
            
            foreach ($teamLeadsAndAdmins as $manager) {
                $manager->notify(new TicketNotification($ticket, 'Ticket Resolved', "Ticket #{$ticket->reference_number} has been resolved by {$resolver->name}."));
            }
        });
    }

    /**
     * Reopen a resolved ticket.
     */
    public function reopen(Ticket $ticket, User $user): bool
    {
        if (!$ticket->canBeReopened()) {
            return false;
        }

        $ticket->update([
            'status'       => 'reopened',
            'reopened_at'  => now(),
            'reopen_count' => $ticket->reopen_count + 1,
            'resolved_at'  => null,
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => $user->id,
            'action'    => 'reopened',
        ]);

        // Notify Assignee and Team Lead
        if ($ticket->assignee) {
            $ticket->assignee->notify(new TicketNotification($ticket, 'Ticket Reopened', "Ticket #{$ticket->reference_number} has been reopened by {$user->name}."));
        }
        $teamLeads = User::byRole('team_lead')->get();
        foreach ($teamLeads as $lead) {
            $lead->notify(new TicketNotification($ticket, 'Ticket Reopened', "Ticket #{$ticket->reference_number} has been reopened."));
        }

        return true;
    }

    /**
     * Close a ticket.
     */
    public function close(Ticket $ticket, User $actor): void
    {
        $ticket->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => $actor->id,
            'action'    => 'closed',
        ]);

        if ($ticket->creator) {
            $ticket->creator->notify(new TicketNotification($ticket, 'Ticket Closed', "Your ticket #{$ticket->reference_number} has been permanently closed."));
        }
    }

    /**
     * Cancel a ticket.
     */
    public function cancel(Ticket $ticket, User $actor): void
    {
        $ticket->update(['status' => 'cancelled']);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => $actor->id,
            'action'    => 'cancelled',
        ]);
    }
}
