<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $ticket,
        public $action,
        public $message
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ETMS Notification: ' . $this->action . ' (#' . $this->ticket->reference_number . ')')
            ->view('emails.ticket_notification', [
                'action'              => $this->action,
                'notificationMessage' => $this->message,
                'ticket'              => $this->ticket,
                'url'                 => route('tickets.show', $this->ticket)
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id'        => $this->ticket->id,
            'reference_number' => $this->ticket->reference_number,
            'title'            => $this->ticket->title,
            'action'           => $this->action,
            'message'          => $this->message,
            'url'              => route('tickets.show', $this->ticket),
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('TicketNotification failed to send: ' . $exception->getMessage(), [
            'ticket_id' => $this->ticket->id ?? null,
            'action'    => $this->action ?? null,
        ]);
    }
}
