<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketStatus $oldStatus,
        public readonly TicketStatus $newStatus,
        public readonly ?string $actorName,
    ) {}

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
            'old_status' => $this->oldStatus->label(),
            'new_status' => $this->newStatus->label(),
            'actor_name' => $this->actorName,
            'message' => 'Ticket '.$this->ticket->tracking_code.' atualizado para '.$this->newStatus->label().'.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Ticket '.$this->ticket->tracking_code.' atualizado')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('O ticket **'.$this->ticket->tracking_code.'** teve o status atualizado de '.$this->oldStatus->label().' para **'.$this->newStatus->label().'**.')
            ->action('Ver ticket', route('admin.tickets.show', $this->ticket));

        if ($this->actorName !== null) {
            $mail->line('Atualizado por: '.$this->actorName);
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
