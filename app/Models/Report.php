<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'generated_by', 'name', 'type', 'filters',
        'file_path', 'format', 'status', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters'      => 'array',
            'completed_at' => 'datetime',
            'created_at'   => 'datetime',
        ];
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getDownloadUrlAttribute(): ?string
    {
        return $this->file_path ? route('reports.download', $this->id) : null;
    }
}
