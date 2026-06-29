<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    // Table: 'ticket_histories' (Laravel default plural — matches MySQL)
    public $timestamps = false;

    protected $fillable = [
        'ticket_id', 'actor_id', 'action', 'field', 'old_value', 'new_value', 'note',
    ];

    protected $dates = ['created_at'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get a human-readable description of this history event.
     */
    public function getDescriptionAttribute(): string
    {
        $actor = $this->actor?->name ?? 'System';

        return match ($this->action) {
            'created'        => "{$actor} created the ticket",
            'status_changed' => "{$actor} changed status from {$this->old_value} to {$this->new_value}",
            'assigned'       => "{$actor} assigned the ticket",
            'reassigned'     => "{$actor} reassigned the ticket",
            'comment_added'  => "{$actor} added a comment",
            'note_added'     => "{$actor} added an internal note",
            'escalated'      => "{$actor} escalated the ticket",
            'resolved'       => "{$actor} resolved the ticket",
            'closed'         => "{$actor} closed the ticket",
            'reopened'       => "{$actor} reopened the ticket",
            'cancelled'      => "{$actor} cancelled the ticket",
            'attachment'     => "{$actor} added an attachment",
            default          => "{$actor} performed action: {$this->action}",
        };
    }

    /**
     * Get Font Awesome icon for this history action.
     */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created'        => 'fa-plus-circle text-primary',
            'status_changed' => 'fa-exchange-alt text-info',
            'assigned'       => 'fa-user-check text-success',
            'escalated'      => 'fa-arrow-up text-danger',
            'resolved'       => 'fa-check-circle text-success',
            'closed'         => 'fa-lock text-secondary',
            'reopened'       => 'fa-undo text-warning',
            'cancelled'      => 'fa-times-circle text-dark',
            'comment_added'  => 'fa-comment text-info',
            'attachment'     => 'fa-paperclip text-muted',
            default          => 'fa-circle text-muted',
        };
    }
}
