<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category_id', 'priority_id',
        'response_time_hours', 'resolution_time_hours',
        'escalation_after_hours', 'business_hours_only', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'business_hours_only' => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'sla_policy_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
