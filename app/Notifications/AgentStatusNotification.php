<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class AgentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $agent,
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'agent_id' => $this->agent->id,
            'agent_name' => $this->agent->name,
            'status' => $this->status,
            'message' => "Agent {$this->agent->name} is now " . str_replace('_', ' ', $this->status) . ".",
            'url' => route('dashboard'),
        ];
    }
}
