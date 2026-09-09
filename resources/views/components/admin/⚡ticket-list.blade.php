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

    #[Computed]
    public function statusDotColors(): array
    {
        return [
            'aberto' => 'bg-amber-500',
            'em_andamento' => 'bg-blue-500',
            'resolvido' => 'bg-green-500',
        ];
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
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">Tickets</flux:heading>
            <flux:text class="mt-1">Acompanhe os chamados registrados pelos técnicos e gerencie o atendimento.</flux:text>
        </div>

        @if ($this->status !== null || $this->search !== '')
            <div>
                <flux:button
                    variant="subtle"
                    size="sm"
                    wire:click="$set('search', ''); $set('status', null)"
                >
                    Limpar filtros
                </flux:button>
            </div>
        @endif
    </div>

    <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div
            role="group"
            aria-label="Filtrar por status"
            class="flex flex-wrap items-center gap-1 rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700/60 dark:bg-zinc-800/70"
        >
            @php
                $filterButtonClasses = fn ($active) => 'inline-flex h-9 cursor-pointer items-center gap-2 whitespace-nowrap rounded-lg px-3 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-zinc-400 dark:focus-visible:outline-zinc-500 ' . ($active
                    ? 'bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900'
                    : 'text-zinc-500 hover:bg-zinc-200/40 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white');
            @endphp

            <button
                type="button"
                wire:key="filter-all"
                wire:click="$set('status', null)"
                aria-pressed="{{ $this->status === null ? 'true' : 'false' }}"
                class="{{ $filterButtonClasses($this->status === null) }}"
            >
                Todos
                <span class="text-xs font-semibold tracking-tight">{{ $this->counts['total'] }}</span>
            </button>

            @foreach ($this->statuses as $status)
                <button
                    type="button"
                    wire:key="filter-{{ $status->value }}"
                    wire:click="$set('status', '{{ $status->value }}')"
                    aria-pressed="{{ $this->status === $status->value ? 'true' : 'false' }}"
                    class="{{ $filterButtonClasses($this->status === $status->value) }}"
                >
                    <span class="size-1.5 shrink-0 rounded-full {{ $this->statusDotColors[$status->value] }}"></span>
                    {{ $status->label() }}
                    <span class="text-xs font-semibold tracking-tight">{{ $this->counts[$status->value] }}</span>
                </button>
            @endforeach
        </div>

        <div class="w-full lg:w-80">
            <flux:input
                wire:model.live="search"
                clearable
                icon="magnifying-glass"
                placeholder="Buscar por código, site ou técnico..."
            />
        </div>
    </div>

    @if ($this->counts['total'] === 0)
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="ticket" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">Nenhum ticket ainda</flux:heading>
            <flux:text class="mt-1">Os chamados abertos pelos técnicos aparecerão aqui assim que forem registrados.</flux:text>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed :paginate="$this->tickets">
                <flux:table.columns>
                    <flux:table.column scope="col">Código</flux:table.column>
                    <flux:table.column scope="col">Site</flux:table.column>
                    <flux:table.column scope="col">Técnico</flux:table.column>
                    <flux:table.column scope="col">Check-in</flux:table.column>
                    <flux:table.column scope="col">Status</flux:table.column>
                    <flux:table.column scope="col">Aberto em</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->tickets as $ticket)
                        <flux:table.row
                            wire:key="ticket-{{ $ticket->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell variant="strong">
                                <a
                                    href="{{ route('admin.tickets.show', $ticket) }}"
                                    wire:navigate
                                    class="font-mono text-sm font-semibold underline-offset-2 hover:underline"
                                >{{ $ticket->tracking_code }}</a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $ticket->site_id }}</flux:table.cell>
                            <flux:table.cell>{{ $ticket->technician_name }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($ticket->checked_in)
                                    <flux:badge color="emerald" size="sm">Feito</flux:badge>
                                @else
                                    <flux:badge size="sm">Pendente</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $ticket->status->color() }}" size="sm">
                                    {{ $ticket->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>{{ $ticket->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $ticket->created_at->format('H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon-only
                                    icon="arrow-right"
                                    :href="route('admin.tickets.show', $ticket)"
                                    wire:navigate
                                    :aria-label="'Ver ticket ' . $ticket->tracking_code"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" align="center">
                                <div class="py-12">
                                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                                        <flux:icon name="magnifying-glass" class="size-5" />
                                    </div>
                                    <flux:heading size="sm" class="mt-3">Nenhum ticket encontrado</flux:heading>
                                    <flux:text class="mt-1">Nenhum registro corresponde à busca ou aos filtros aplicados.</flux:text>
                                    <div class="mt-4">
                                        <flux:button
                                            variant="subtle"
                                            size="sm"
                                            wire:click="$set('search', ''); $set('status', null)"
                                        >
                                            Limpar filtros
                                        </flux:button>
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>