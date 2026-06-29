<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id', 'comment_id', 'uploaded_by',
        'filename', 'original_name', 'mime_type', 'file_size', 'disk', 'path',
    ];

    protected $appends = ['download_url', 'human_size'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function comment()
    {
        return $this->belongsTo(TicketComment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Download URL – routed through auth-protected controller.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('attachments.download', $this->id);
    }

    /**
     * Human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024)      return $bytes . ' B';
        if ($bytes < 1048576)   return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * Check if this attachment is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
}
