<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'url', 'ip_address', 'user_agent', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Log an event to the audit trail.
     */
    public static function record(
        string  $event,
        ?int    $userId = null,
        ?string $auditableType = null,
        ?int    $auditableId = null,
        array   $oldValues = [],
        array   $newValues = []
    ): self {
        return static::create([
            'user_id'        => $userId ?? auth()->id(),
            'event'          => $event,
            'auditable_type' => $auditableType,
            'auditable_id'   => $auditableId,
            'old_values'     => $oldValues ?: null,
            'new_values'     => $newValues ?: null,
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
