<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = ['ticket_id', 'assigned_to', 'assigned_by', 'action', 'reason'];

    protected $dates = ['created_at'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
