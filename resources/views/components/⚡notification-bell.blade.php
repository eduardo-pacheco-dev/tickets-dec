<?php

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public string $display = 'sidebar';

    public bool $pushEnabled = false;

    public function mount(): void
    {
        $this->pushEnabled = $this->user()?->pushSubscriptions()->exists() ?? false;
    }

    private function user(): ?object
    {
        return auth()->user();
    }

    public function unreadCount(): int
    {
        return $this->user()?->notifications()->whereNull('read_at')->count() ?? 0;
    }

    public function recentNotifications(): Collection
    {
        return $this->user()?->notifications()->latest()->take(8)->get() ?? new Collection;
    }

    public function openNotification(string $notificationId): void
    {
        $notification = $this->user()?->notifications()->firstWhere('id', $notificationId);

        if (! $notification instanceof DatabaseNotification) {
            return;
        }

        $notification->markAsRead();

        $ticket = Ticket::find($notification->data['ticket_id'] ?? null);

        $this->redirect($ticket !== null
            ? route('admin.tickets.show', $ticket)
            : route('admin.tickets.index'));
    }

    public function markAllAsRead(): void
    {
        $this->user()?->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    #[On('push-subscription-saved')]
    public function onPushSubscriptionSaved(): void
    {
        $this->pushEnabled = true;
    }

    public function disableBrowserNotifications(): void
    {
        $this->user()?->pushSubscriptions()->delete();
        $this->pushEnabled = false;
    }
};
?>

<div>
    @if ($this->user())
        <flux:dropdown position="bottom" align="start" class="w-full">
            @if ($this->display === 'header')
                <button
                    type="button"
                    class="relative inline-flex items-center justify-center rounded-lg p-2 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white"
                    data-test="notification-bell"
                >
                    <flux:icon name="bell" class="size-5" variant="outline" />
                    @if ($this->unreadCount() > 0)
                        <span class="absolute -end-0.5 -top-0.5 flex size-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white" data-test="notification-badge">
                            {{ min($this->unreadCount(), 99) }}
                        </span>
                    @endif
                </button>
            @else
                <button
                    type="button"
                    class="relative my-px flex h-8 w-full items-center gap-3 rounded-lg border border-transparent px-3 py-0 text-start text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-white/80 dark:hover:bg-white/5 dark:hover:text-white in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:justify-center"
                    data-test="notification-bell"
                >
                    <span class="relative">
                        <flux:icon name="bell" class="size-4" variant="outline" />
                        @if ($this->unreadCount() > 0)
                            <span class="absolute -end-1.5 -top-1.5 flex size-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white" data-test="notification-badge">
                                {{ min($this->unreadCount(), 99) }}
                            </span>
                        @endif
                    </span>
                    <span class="in-data-flux-sidebar-collapsed-desktop:not-in-data-flux-sidebar-group-dropdown:hidden flex-1 truncate text-sm font-medium">Notificações</span>
                </button>
            @endif

            <flux:menu class="w-80! max-w-[calc(100vw-2rem)]">
                <div class="flex items-center justify-between gap-3 px-3 py-2">
                    <flux:heading size="sm">Notificações</flux:heading>
                    @if ($this->unreadCount() > 0)
                        <flux:button
                            as="button"
                            variant="subtle"
                            size="xs"
                            wire:click="markAllAsRead"
                            data-test="mark-all-read"
                        >
                            Marcar todas como lidas
                        </flux:button>
                    @endif
                </div>

                <flux:menu.separator />

                @if ($this->recentNotifications()->isEmpty())
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-white/5">
                            <flux:icon name="bell-slash" class="size-5 text-zinc-400 dark:text-zinc-500" />
                        </div>
                        <flux:text class="mt-3 text-sm">Nenhuma notificação ainda.</flux:text>
                    </div>
                @else
                    <flux:menu.radio.group class="max-h-96 overflow-y-auto">
                        @foreach ($this->recentNotifications() as $notification)
                            <flux:menu.item
                                as="button"
                                wire:click="openNotification('{{ $notification->id }}')"
                                class="items-start! py-2.5!"
                                wire:key="notif-{{ $notification->id }}"
                            >
                                <div class="grid min-w-0 flex-1 gap-0.5 text-start">
                                    <p class="text-sm leading-snug text-zinc-700 dark:text-zinc-200">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $notification->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                @if ($notification->read_at === null)
                                    <span class="size-2 rounded-full bg-blue-500" data-test="unread-dot"></span>
                                @endif
                            </flux:menu.item>
                        @endforeach
                    </flux:menu.radio.group>
                @endif

                <flux:menu.separator />

                <div class="flex items-center gap-2 px-3 py-2.5">
                    @if ($this->pushEnabled)
                        <flux:icon name="bell" class="size-4 shrink-0 text-green-600 dark:text-green-400" />
                        <flux:text size="sm" class="flex-1">Notificações do navegador ativadas.</flux:text>
                        <flux:button
                            as="button"
                            variant="subtle"
                            size="xs"
                            wire:click="disableBrowserNotifications"
                            data-test="disable-push"
                        >
                            Desativar
                        </flux:button>
                    @else
                        <flux:icon name="bell" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
                        <flux:text size="sm" class="flex-1">Receber alertas no navegador?</flux:text>
                        <flux:button
                            as="button"
                            variant="primary"
                            size="xs"
                            x-on:click="window.enablePushNotifications()"
                            data-test="enable-push"
                        >
                            Ativar
                        </flux:button>
                    @endif
                </div>
            </flux:menu>
        </flux:dropdown>
    @endif
</div>