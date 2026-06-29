<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'display_name', 'color', 'icon',
        'sla_hours_response', 'sla_hours_resolve', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }

    public function slaPolicies()
    {
        return $this->hasMany(SlaPolicy::class, 'priority_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
