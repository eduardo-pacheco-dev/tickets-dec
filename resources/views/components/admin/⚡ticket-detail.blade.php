<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use App\Notifications\TicketResponseSavedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;
    public string $admin_response = '';

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->admin_response = $this->ticket->admin_response ?? '';
    }

    #[Computed]
    public function statuses(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketStatus::query()->active()->orderBy('sort_order')->orderBy('label')->get();
    }

    #[Computed]
    public function currentIndex(): int
    {
        return $this->statuses->search(fn (TicketStatus $status) => $status->name === $this->ticket->status);
    }

    #[Computed]
    public function nextStatus(): ?TicketStatus
    {
        $index = $this->currentIndex;

        if ($index === false) {
            return $this->statuses->first();
        }

        return $this->statuses->get($index + 1);
    }

    #[Computed]
    public function previousStatus(): ?TicketStatus
    {
        $index = $this->currentIndex;

        if ($index === false) {
            return null;
        }

        return $this->statuses->get($index - 1);
    }

    public function saveResponse(): void
    {
        $this->ensureCanManageTickets();

        $this->validate([
            'admin_response' => ['required', 'string', 'max:2000'],
        ]);

        $this->ticket->update([
            'admin_response' => $this->admin_response,
        ]);

        $this->notifyStaff(new TicketResponseSavedNotification(
            $this->ticket,
            $this->admin_response,
            auth()->user()->name,
        ));

        $this->dispatch('response-saved');
    }

    public function advanceStatus(): void
    {
        $this->ensureCanManageTickets();

        if (! $this->nextStatus) {
            return;
        }

        $oldStatus = $this->ticket->status;
        $newStatus = $this->nextStatus->name;

        $this->ticket->update([
            'status' => $newStatus,
        ]);

        $this->recordStatusHistory($oldStatus, $newStatus);

        $this->notifyStaff(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            $newStatus,
            auth()->user()->name,
        ));

        $this->dispatch('status-updated');
    }

    public function regressStatus(): void
    {
        $this->ensureCanManageTickets();

        if (! $this->previousStatus) {
            return;
        }

        $oldStatus = $this->ticket->status;
        $newStatus = $this->previousStatus->name;

        $this->ticket->update([
            'status' => $newStatus,
        ]);

        $this->recordStatusHistory($oldStatus, $newStatus);

        $this->notifyStaff(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            $newStatus,
            auth()->user()->name,
        ));

        $this->dispatch('status-updated');
    }

    private function recordStatusHistory(string $oldStatus, string $newStatus): void
    {
        $this->ticket->statusHistories()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => auth()->id(),
        ]);
    }

    private function ensureCanManageTickets(): void
    {
        $user = auth()->user();

        if (! $user->role->canManageTickets()) {
            abort(403);
        }
    }

    private function notifyStaff(Notification $notification): void
    {
        $recipients = User::query()->ticketStaff()->whereKeyNot(auth()->id())->get();

        if ($recipients->isEmpty()) {
            return;
        }

        NotificationFacade::send($recipients, $notification);
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('admin.tickets.index') }}" wire:navigate>
            {{ __('Voltar') }}
        </flux:button>
        <flux:heading size="lg">{{ __('Ticket') }} {{ $this->ticket->tracking_code }}</flux:heading>
        <flux:badge color="{{ $this->ticket->statusColor() }}">
            {{ $this->ticket->statusLabel() }}
        </flux:badge>
        @if ($position = $this->ticket->queuePosition())
            <flux:badge color="zinc" size="md">
                <flux:icon name="queue-list" class="size-3.5" />
                {{ __('Fila') }} #{{ $position }}
            </flux:badge>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">{{ __('Informações do Ticket') }}</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">{{ __('Site ID') }}</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->site_id }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">{{ __('Técnico') }}</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->technician_name }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">{{ __('Check-in') }}</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->checked_in ? __('Sim') : __('Não') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">{{ __('Aberto em') }}</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->created_at->format('d/m/Y H:i') }}</flux:text>
                    </div>
                </div>

                <div class="mt-4">
                    <flux:text class="text-xs font-medium uppercase text-gray-500">{{ __('Relatório(s) Solicitado(s)') }}</flux:text>
                    @if ($this->ticket->reportTypes->isNotEmpty())
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach ($this->ticket->reportTypes as $reportType)
                                <flux:badge color="blue" size="sm">{{ $reportType->name }}</flux:badge>
                            @endforeach
                        </div>
                    @endif
                    @if ($this->ticket->report_description)
                        <flux:text class="mt-1">{{ $this->ticket->report_description }}</flux:text>
                    @endif
                </div>
            </div>

            @if (auth()->user()->role->canManageTickets())
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                    <flux:heading size="sm" class="mb-4">{{ __('Resposta do Analista') }}</flux:heading>
                    <form wire:submit="saveResponse" class="space-y-4">
                        <flux:textarea
                            wire:model="admin_response"
                            rows="4"
                            placeholder="{{ __('Escreva sua resposta ao técnico...') }}"
                        />
                        <flux:error name="admin_response" />
                        <flux:button type="submit" variant="primary">
                            {{ __('Salvar Resposta') }}
                        </flux:button>
                    </form>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if (auth()->user()->role->canManageTickets())
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                    <flux:heading size="sm" class="mb-4">{{ __('Alterar Status') }}</flux:heading>

                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-100 bg-zinc-50/60 px-4 py-3 dark:border-zinc-700/60 dark:bg-white/[3%]">
                        <flux:text class="text-xs font-medium uppercase text-zinc-400">{{ __('Status atual') }}</flux:text>
                        <flux:badge color="{{ $this->ticket->statusColor() }}" size="md">
                            {{ $this->ticket->statusLabel() }}
                        </flux:badge>
                    </div>

                    <div class="mt-2 flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500">
                        <flux:icon :name="$this->nextStatus ? 'arrow-trending-up' : 'flag'" class="size-3.5" />
                        @if ($this->nextStatus)
                            {{ __('Próximo:') }} <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $this->nextStatus->label }}</span>
                        @else
                            {{ __('Este é o status final.') }}
                        @endif
                    </div>

                    <div class="mt-4 grid gap-2">
                        <flux:button
                            variant="primary"
                            icon-trailing="arrow-right"
                            wire:click="advanceStatus"
                            :disabled="! $this->nextStatus"
                            :aria-label="__('Avançar status')"
                            class="w-full"
                        >
                            {{ __('Avançar') }}
                            @if ($this->nextStatus)
                                <span class="opacity-70">· {{ $this->nextStatus->label }}</span>
                            @endif
                        </flux:button>

                        <flux:button
                            variant="subtle"
                            icon="arrow-left"
                            wire:click="regressStatus"
                            :disabled="! $this->previousStatus"
                            :aria-label="__('Status anterior')"
                            class="w-full"
                        >
                            {{ __('Voltar') }}
                        </flux:button>
                    </div>
                </div>
            @endif

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">{{ __('Timeline') }}</flux:heading>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 shrink-0 rounded-full bg-green-500"></div>
                        <div>
                            <flux:text class="text-sm font-medium">{{ __('Ticket criado') }}</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $this->ticket->created_at->format('d/m/Y H:i') }}</flux:text>
                        </div>
                    </div>

                    @foreach ($this->ticket->statusHistories as $history)
                        <div wire:key="history-{{ $history->id }}" class="flex items-start gap-3">
                            <div class="mt-1 size-2 shrink-0 rounded-full bg-blue-500"></div>
                            <div>
                                <flux:text class="text-sm font-medium">
                                    {{ $history->fromLabel() }} <flux:icon name="arrow-right" class="mx-1 inline size-3 text-zinc-400" /> {{ $history->toLabel() }}
                                </flux:text>
                                <flux:text class="text-xs text-gray-500">
                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                    @if ($history->changer)
                                        · {{ __('por') }} {{ $history->changer->name }}
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
