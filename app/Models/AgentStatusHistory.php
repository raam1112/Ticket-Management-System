<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'changed_by',
        'reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    protected static function booted()
    {
        static::created(function ($history) {
            $agent = $history->user;
            if (!$agent) return;

            $teamLeads = User::byRole('team_lead')->active()->get();
            $admins = User::byRole('admin')->active()->get();
            $notifiables = $teamLeads->merge($admins);

            foreach ($notifiables as $notifyUser) {
                $notifyUser->notify(new \App\Notifications\AgentStatusNotification($agent, $history->status));
            }
        });
    }
}
