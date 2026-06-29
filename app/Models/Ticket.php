<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number', 'category_id', 'priority_id', 'created_by',
        'assigned_to', 'assigned_by', 'department_id', 'sla_policy_id',
        'title', 'description', 'status', 'resolution_note', 'resolution_level',
        'sla_response_at', 'sla_resolve_at', 'sla_response_met', 'sla_resolve_met',
        'first_response_at', 'resolved_at', 'closed_at', 'reopened_at',
        'last_activity_at', 'reopen_count', 'escalation_count',
        'satisfaction_rating', 'satisfaction_note', 'source', 'is_internal', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags'              => 'array',
            'sla_response_at'   => 'datetime',
            'sla_resolve_at'    => 'datetime',
            'first_response_at' => 'datetime',
            'resolved_at'       => 'datetime',
            'closed_at'         => 'datetime',
            'reopened_at'       => 'datetime',
            'last_activity_at'  => 'datetime',
            'sla_response_met'  => 'boolean',
            'sla_resolve_met'   => 'boolean',
            'is_internal'       => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function publicComments()
    {
        return $this->hasMany(TicketComment::class)
                    ->where('is_internal', false)
                    ->whereNull('parent_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function assignments()
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function escalations()
    {
        return $this->hasMany(TicketEscalation::class);
    }

    public function history()
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at', 'asc');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['closed', 'cancelled', 'resolved']);
    }

    public function scopeSlaAtRisk($query, int $withinHours = 2)
    {
        return $query->whereNotIn('status', ['resolved', 'closed', 'cancelled'])
                     ->where('sla_resolve_at', '<=', now()->addHours($withinHours))
                     ->where('sla_resolve_at', '>', now());
    }

    public function scopeSlaBreached($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed', 'cancelled'])
                     ->where('sla_resolve_at', '<', now());
    }

    public function scopeForUser($query, User $user)
    {
        return match ($user->primary_role) {
            'admin'     => $query,
            'team_lead' => $query, // Team leads see all tickets so they can assign to agents
            'agent'     => $query->where('assigned_to', $user->id),
            default     => $query->where('created_by', $user->id),
        };
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Get the SLA progress percentage (0-100) for the resolve deadline.
     */
    public function getSlaProgressAttribute(): int
    {
        if (!$this->created_at || !$this->sla_resolve_at) return 0;

        $total   = $this->created_at->diffInSeconds($this->sla_resolve_at);
        $elapsed = $this->created_at->diffInSeconds(now());

        if ($total <= 0) return 100;
        return min(100, (int) round(($elapsed / $total) * 100));
    }

    /**
     * Get whether this ticket has breached its resolve SLA.
     */
    public function getIsSlaBreachedAttribute(): bool
    {
        return $this->sla_resolve_at && now()->gt($this->sla_resolve_at)
            && !in_array($this->status, ['resolved', 'closed', 'cancelled']);
    }

    /**
     * Get a human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'         => 'Open',
            'assigned'     => 'Assigned',
            'in_progress'  => 'In Progress',
            'pending_user' => 'Pending User',
            'escalated'    => 'Escalated',
            'under_review' => 'Under Review',
            'resolved'     => 'Resolved',
            'closed'       => 'Closed',
            'reopened'     => 'Reopened',
            'cancelled'    => 'Cancelled',
            default        => ucfirst($this->status),
        };
    }

    /**
     * Get Bootstrap badge class for the status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open'         => 'badge-primary',
            'assigned'     => 'badge-indigo',
            'in_progress'  => 'badge-info',
            'pending_user' => 'badge-warning',
            'escalated'    => 'badge-danger',
            'under_review' => 'badge-purple',
            'resolved'     => 'badge-success',
            'closed'       => 'badge-secondary',
            'reopened'     => 'badge-orange',
            'cancelled'    => 'badge-dark',
            default        => 'badge-secondary',
        };
    }

    // ─── Business Logic Helpers ───────────────────────────────────────────────

    /**
     * Check if the ticket can be reopened (within 7 days of resolution).
     */
    public function canBeReopened(): bool
    {
        return $this->status === 'resolved'
            && $this->resolved_at
            && $this->resolved_at->diffInDays(now()) <= 7;
    }

    /**
     * Check if a user can cancel this ticket.
     */
    public function canBeCancelledBy(User $user): bool
    {
        if ($user->hasRole('admin')) return true;
        return $this->created_by === $user->id
            && in_array($this->status, ['open', 'assigned']);
    }

    // ─── Boot: Auto-generate reference number ─────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->reference_number)) {
                $year  = now()->format('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $ticket->reference_number = sprintf('TKT-%s-%06d', $year, $count);
            }
            $ticket->last_activity_at = now();
        });

        static::updating(function (Ticket $ticket) {
            $ticket->last_activity_at = now();
        });
    }
}
