<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    public function __construct(public readonly Ticket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'tracking_code' => $this->ticket->tracking_code,
            'site_id' => $this->ticket->site_id,
            'technician_name' => $this->ticket->technician_name,
            'message' => 'Novo ticket '.$this->ticket->tracking_code.' aberto por '.$this->ticket->technician_name.'.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo ticket '.$this->ticket->tracking_code)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Um novo ticket foi aberto no sistema:')
            ->line('**'.$this->ticket->tracking_code.'** — '.$this->ticket->report_description)
            ->line('Técnico: '.$this->ticket->technician_name.' · Site: '.$this->ticket->site_id)
            ->action('Ver ticket', route('admin.tickets.show', $this->ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
