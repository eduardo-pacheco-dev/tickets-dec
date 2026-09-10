---
paths:
  - 'app/Notifications/**, resources/views/components/**'
---

# Views Components

## Refer to the Web Push channel by class; toWebPush returns WebPushMessage
Web Push: referencie o canal como NotificationChannels\WebPush\WebPushChannel::class em via() (não existe nome curto "webpush"). toWebPush($notifiable, $notification) deve retornar WebPushMessage; o payload JSON enviado tem title/body/data no topo (data['url'] = route('admin.tickets.show', $ticket), lida no service worker public/sw.js). Use ->options(['TTL' => 86400]) para não empilhar notificações obsoletas. Para testar o envio sem rede nem OpenSSL, faça um fake por herança de Minishlink\WebPush\WebPush sobrescrevendo queueNotification/flush (NÃO estenda o canal nem use Mockery na resolution contextual).
