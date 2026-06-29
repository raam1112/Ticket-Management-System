<?php

namespace App\Services;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Carbon\Carbon;

/**
 * SlaService — handles SLA deadline calculation and breach detection.
 */
class SlaService
{
    /**
     * Find and apply the best SLA policy for a ticket.
     * Priority: category+priority > priority-only > category-only > any active
     */
    public function applySlaPolicyToTicket(Ticket $ticket): void
    {
        $policy = SlaPolicy::active()
            ->where(function ($q) use ($ticket) {
                $q->where('category_id', $ticket->category_id)
                  ->where('priority_id', $ticket->priority_id);
            })
            ->orWhere(function ($q) use ($ticket) {
                $q->where('priority_id', $ticket->priority_id)
                  ->whereNull('category_id');
            })
            ->orWhere(function ($q) use ($ticket) {
                $q->where('category_id', $ticket->category_id)
                  ->whereNull('priority_id');
            })
            ->orderByRaw('(category_id IS NOT NULL AND priority_id IS NOT NULL) DESC')
            ->first();

        if (!$policy) {
            // Fall back to priority defaults
            $priority = $ticket->priority;
            if ($priority) {
                $ticket->sla_response_at = now()->addHours($priority->sla_hours_response);
                $ticket->sla_resolve_at  = now()->addHours($priority->sla_hours_resolve);
            }
        } else {
            $ticket->sla_policy_id  = $policy->id;
            $ticket->sla_response_at = now()->addHours($policy->response_time_hours);
            $ticket->sla_resolve_at  = now()->addHours($policy->resolution_time_hours);
        }
    }

    /**
     * Mark first response SLA as met/missed when an agent first responds.
     */
    public function recordFirstResponse(Ticket $ticket): void
    {
        if ($ticket->first_response_at) return; // Already recorded

        $ticket->first_response_at = now();
        $ticket->sla_response_met  = $ticket->sla_response_at
            ? now()->lte($ticket->sla_response_at)
            : true;
        $ticket->save();
    }

    /**
     * Mark resolution SLA as met/missed when ticket is resolved.
     */
    public function recordResolution(Ticket $ticket): void
    {
        $ticket->sla_resolve_met = $ticket->sla_resolve_at
            ? now()->lte($ticket->sla_resolve_at)
            : true;
    }

    /**
     * Get all tickets that have breached their SLA and are still open.
     */
    public function getBreachedTickets()
    {
        return $this->getBreachedTicketsQuery()
            ->with(['creator', 'assignee', 'priority', 'category'])
            ->get();
    }

    /**
     * Get query for tickets that have breached their SLA and are still open.
     */
    public function getBreachedTicketsQuery()
    {
        return Ticket::slaBreached();
    }

    /**
     * Get tickets at risk of SLA breach within the next N hours.
     */
    public function getAtRiskTickets(int $withinHours = 2)
    {
        return Ticket::slaAtRisk($withinHours)
            ->with(['creator', 'assignee', 'priority', 'category'])
            ->orderBy('sla_resolve_at')
            ->get();
    }

    /**
     * Calculate overall SLA compliance percentage for a date range.
     */
    public function getComplianceRate(?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = Ticket::whereIn('status', ['resolved', 'closed']);
        if ($from) $query->where('created_at', '>=', $from);
        if ($to)   $query->where('created_at', '<=', $to);

        $total   = $query->count();
        $met     = (clone $query)->where('sla_resolve_met', true)->count();

        return $total > 0 ? round(($met / $total) * 100, 2) : 100.0;
    }
}
