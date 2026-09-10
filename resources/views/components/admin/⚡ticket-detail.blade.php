<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketResponseSavedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Component;

new class extends Component
{
    public Ticket $ticket;
    public string $admin_response = '';
    public ?string $new_status = null;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->admin_response = $this->ticket->admin_response ?? '';
        $this->new_status = $this->ticket->status->value;
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

    public function updateStatus(): void
    {
        $this->ensureCanManageTickets();

        $this->validate([
            'new_status' => ['required', 'string'],
        ]);

        $status = TicketStatus::from($this->new_status);
        $oldStatus = $this->ticket->status;

        $this->ticket->update([
            'status' => $status,
        ]);

        $this->notifyStaff(new TicketStatusUpdatedNotification(
            $this->ticket,
            $oldStatus,
            $status,
            auth()->user()->name,
        ));

        $this->dispatch('status-updated');
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
            Voltar
        </flux:button>
        <flux:heading size="lg">Ticket {{ $this->ticket->tracking_code }}</flux:heading>
        <flux:badge color="{{ $this->ticket->status->color() }}">
            {{ $this->ticket->status->label() }}
        </flux:badge>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Informações do Ticket</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Site ID</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->site_id }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Técnico</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->technician_name }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Check-in</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->checked_in ? 'Sim' : 'Não' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Aberto em</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->ticket->created_at->format('d/m/Y H:i') }}</flux:text>
                    </div>
                </div>

                <div class="mt-4">
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Relatório Solicitado</flux:text>
                    @if ($this->ticket->reportType)
                        <flux:badge color="blue" size="sm" class="mt-1">{{ $this->ticket->reportType->name }}</flux:badge>
                    @endif
                    @if ($this->ticket->report_description)
                        <flux:text class="mt-1">{{ $this->ticket->report_description }}</flux:text>
                    @endif
                </div>
            </div>

            @if (auth()->user()->role->canManageTickets())
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                    <flux:heading size="sm" class="mb-4">Resposta do Analista</flux:heading>
                    <form wire:submit="saveResponse" class="space-y-4">
                        <flux:textarea
                            wire:model="admin_response"
                            rows="4"
                            placeholder="Escreva sua resposta ao técnico..."
                        />
                        <flux:error name="admin_response" />
                        <flux:button type="submit" variant="primary">
                            Salvar Resposta
                        </flux:button>
                    </form>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if (auth()->user()->role->canManageTickets())
                <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                    <flux:heading size="sm" class="mb-4">Alterar Status</flux:heading>
                    <form wire:submit="updateStatus" class="space-y-4">
                        <flux:select wire:model="new_status">
                            @foreach (TicketStatus::cases() as $status)
                                <flux:select.option value="{{ $status->value }}">
                                    {{ $status->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="new_status" />
                        <flux:button type="submit" variant="primary" class="w-full">
                            Atualizar Status
                        </flux:button>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Timeline</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 shrink-0 rounded-full bg-green-500"></div>
                        <div>
                            <flux:text class="text-sm font-medium">Ticket criado</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $this->ticket->created_at->format('d/m/Y H:i') }}</flux:text>
                        </div>
                    </div>
                    @if ($this->ticket->updated_at && $this->ticket->updated_at != $this->ticket->created_at)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 size-2 shrink-0 rounded-full bg-blue-500"></div>
                            <div>
                                <flux:text class="text-sm font-medium">Última atualização</flux:text>
                                <flux:text class="text-xs text-gray-500">{{ $this->ticket->updated_at->format('d/m/Y H:i') }}</flux:text>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
