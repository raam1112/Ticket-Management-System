<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id', 'user_id', 'parent_id', 'body', 'is_internal', 'is_edited', 'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_edited'   => 'boolean',
            'edited_at'   => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(TicketComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketComment::class, 'parent_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'comment_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }
}
