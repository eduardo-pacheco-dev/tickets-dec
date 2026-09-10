<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TicketResponseSavedNotification extends Notification
{
    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $response,
        public readonly ?string $actorName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', WebPushChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'tracking_code' => $this->ticket->tracking_code,
            'actor_name' => $this->actorName,
            'message' => 'Nova resposta salva no ticket '.$this->ticket->tracking_code.'.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Nova resposta no ticket '.$this->ticket->tracking_code)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Uma nova resposta foi salva no ticket **'.$this->ticket->tracking_code.'**:')
            ->line((string) str($this->response)->limit(500))
            ->action('Ver ticket', route('admin.tickets.show', $this->ticket));

        if ($this->actorName !== null) {
            $mail->line('Resposta salva por: '.$this->actorName);
        }

        return $mail;
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $body = (string) str($this->response)->limit(120);

        if ($this->actorName !== null) {
            $body .= ' · '.$this->actorName;
        }

        return (new WebPushMessage)
            ->title('Nova resposta no ticket '.$this->ticket->tracking_code)
            ->body($body)
            ->options(['TTL' => 86400])
            ->data([
                'url' => route('admin.tickets.show', $this->ticket),
                'ticket_id' => $this->ticket->id,
                'tracking_code' => $this->ticket->tracking_code,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
