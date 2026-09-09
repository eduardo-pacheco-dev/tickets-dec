<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?string $status = null;

    #[Computed]
    public function counts(): array
    {
        return [
            'total' => Ticket::count(),
            'aberto' => Ticket::where('status', TicketStatus::Aberto)->count(),
            'em_andamento' => Ticket::where('status', TicketStatus::EmAndamento)->count(),
            'resolvido' => Ticket::where('status', TicketStatus::Resolvido)->count(),
        ];
    }

    #[Computed]
    public function statuses(): array
    {
        return TicketStatus::cases();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tickets(): LengthAwarePaginator
    {
        return Ticket::query()
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                        ->orWhere('site_id', 'like', "%{$search}%")
                        ->orWhere('technician_name', 'like', "%{$search}%");
                });
            })
            ->when($this->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">Tickets</flux:heading>
        <flux:text class="text-sm text-gray-500">{{ $this->counts['total'] }} tickets no total</flux:text>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <button
            wire:click="$set('status', null)"
            class="rounded-xl border p-4 text-left transition {{ $this->status === null ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Todos</flux:text>
            <div class="mt-1 text-2xl font-bold">{{ $this->counts['total'] }}</div>
        </button>
        <button
            wire:click="$set('status', 'aberto')"
            class="rounded-xl border p-4 text-left transition {{ $this->status === 'aberto' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Abertos</flux:text>
            <div class="mt-1 text-2xl font-bold text-yellow-600">{{ $this->counts['aberto'] }}</div>
        </button>
        <button
            wire:click="$set('status', 'em_andamento')"
            class="rounded-xl border p-4 text-left transition {{ $this->status === 'em_andamento' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Em Andamento</flux:text>
            <div class="mt-1 text-2xl font-bold text-blue-600">{{ $this->counts['em_andamento'] }}</div>
        </button>
        <button
            wire:click="$set('status', 'resolvido')"
            class="rounded-xl border p-4 text-left transition {{ $this->status === 'resolvido' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Resolvidos</flux:text>
            <div class="mt-1 text-2xl font-bold text-green-600">{{ $this->counts['resolvido'] }}</div>
        </button>
    </div>

    <flux:input
        wire:model.live="search"
        placeholder="Buscar por código, site ou técnico..."
        icon="magnifying-glass"
    />

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Código</flux:table.column>
                <flux:table.column>Site ID</flux:table.column>
                <flux:table.column>Técnico</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Aberto em</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->tickets as $ticket)
                    <flux:table.row wire:key="ticket-{{ $ticket->id }}">
                        <flux:table.cell>
                            <span class="font-mono text-sm font-medium">{{ $ticket->tracking_code }}</span>
                        </flux:table.cell>
                        <flux:table.cell>{{ $ticket->site_id }}</flux:table.cell>
                        <flux:table.cell>{{ $ticket->technician_name }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $ticket->status->color() }}">
                                {{ $ticket->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $ticket->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                variant="ghost"
                                size="sm"
                                :href="route('admin.tickets.show', $ticket)"
                                wire:navigate
                            >
                                Ver detalhes
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center">
                            <flux:text class="py-8 text-gray-500">Nenhum ticket encontrado.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{ $this->tickets->links() }}
</div>