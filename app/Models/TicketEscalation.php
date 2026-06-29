<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketEscalation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id', 'escalated_by', 'escalated_to',
        'reason', 'escalation_type', 'resolved_at', 'resolved_by',
    ];

    protected $dates = ['created_at', 'resolved_at'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function escalatedBy()
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
