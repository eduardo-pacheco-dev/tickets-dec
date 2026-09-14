<?php

use App\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function notifications(): LengthAwarePaginator
    {
        return auth()->user()->notifications()->latest()->paginate(15);
    }

    public function unreadCount(): int
    {
        return auth()->user()->notifications()->whereNull('read_at')->count();
    }

    public function openNotification(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->firstWhere('id', $notificationId);

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
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">{{ __('Notificações') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Acompanhe as movimentações dos tickets do sistema.') }}
            </flux:text>
        </div>

        @if ($this->unreadCount() > 0)
            <div>
                <flux:button
                    variant="subtle"
                    icon="check"
                    wire:click="markAllAsRead"
                    data-test="mark-all-read"
                >
                    {{ __('Marcar todas como lidas') }}
                </flux:button>
            </div>
        @endif
    </div>

    @if (auth()->user()->notifications()->count() === 0)
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="bell-slash" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">{{ __('Nenhuma notificação') }}</flux:heading>
            <flux:text class="mt-1">{{ __('As movimentações dos tickets aparecerão aqui conforme forem acontecendo.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed :paginate="$this->notifications()">
                <flux:table.columns>
                    <flux:table.column scope="col">{{ __('Notificação') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Recebida em') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Status') }}</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->notifications() as $notification)
                        <flux:table.row
                            wire:key="notification-{{ $notification->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell variant="strong">
                                <div>{{ $notification->data['message'] ?? '' }}</div>
                                <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ $notification->data['tracking_code'] ?? '' }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>{{ $notification->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $notification->created_at->format('H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($notification->read_at === null)
                                    <flux:badge color="blue" size="sm">{{ __('Não lida') }}</flux:badge>
                                @else
                                    <flux:badge size="sm">{{ __('Lida') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon-only
                                    icon="arrow-right"
                                    wire:click="openNotification('{{ $notification->id }}')"
                                    aria-label="{{ __('Ver ticket da notificação') }}"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" align="center">
                                <div class="py-12">
                                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                                        <flux:icon name="bell-slash" class="size-5" />
                                    </div>
                                    <flux:heading size="sm" class="mt-3">{{ __('Nenhuma notificação') }}</flux:heading>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>