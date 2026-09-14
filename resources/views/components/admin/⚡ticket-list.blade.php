<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
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

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Computed]
    public function counts(): array
    {
        return [
            'total' => Ticket::count(),
            ...TicketStatus::query()
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(fn (TicketStatus $status) => [
                    $status->name => Ticket::where('status', $status->name)->count(),
                ]),
        ];
    }

    #[Computed]
    public function statuses(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketStatus::query()->active()->orderBy('sort_order')->orderBy('label')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function tickets(): LengthAwarePaginator
    {
        return Ticket::query()
            ->with('reportTypes')
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
            ->orderBy($this->sortBy, $this->sortDirection)
            ->orderBy('id', 'desc')
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">{{ __('Tickets') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Acompanhe os chamados registrados pelos técnicos e gerencie o atendimento.') }}</flux:text>
        </div>

        @if ($this->status !== null || $this->search !== '')
            <div>
                <flux:button
                    variant="subtle"
                    size="sm"
                    wire:click="$wire.set('search', ''); $wire.set('status', null)"
                >
                    {{ __('Limpar filtros') }}
                </flux:button>
            </div>
        @endif
    </div>

    <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div
            role="group"
            aria-label="{{ __('Filtrar por status') }}"
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
                wire:click="$wire.set('status', null)"
                aria-pressed="{{ $this->status === null ? 'true' : 'false' }}"
                class="{{ $filterButtonClasses($this->status === null) }}"
            >
                {{ __('Todos') }}
                <span class="text-xs font-semibold tracking-tight">{{ $this->counts['total'] }}</span>
            </button>

            @foreach ($this->statuses as $status)
                <button
                    type="button"
                    wire:key="filter-{{ $status->name }}"
                    wire:click="$wire.set('status', '{{ $status->name }}')"
                    aria-pressed="{{ $this->status === $status->name ? 'true' : 'false' }}"
                    class="{{ $filterButtonClasses($this->status === $status->name) }}"
                >
                    <span class="size-1.5 shrink-0 rounded-full bg-{{ $status->color }}-500"></span>
                    {{ $status->label }}
                    <span class="text-xs font-semibold tracking-tight">{{ $this->counts[$status->name] }}</span>
                </button>
            @endforeach
        </div>

        <div class="w-full lg:w-80">
            <flux:input
                wire:model.live="search"
                clearable
                icon="magnifying-glass"
                placeholder="{{ __('Buscar por código, site ou técnico...') }}"
            />
        </div>
    </div>

    @if ($this->counts['total'] === 0)
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="ticket" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">{{ __('Nenhum ticket ainda') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Os chamados abertos pelos técnicos aparecerão aqui assim que forem registrados.') }}</flux:text>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed :paginate="$this->tickets">
                <flux:table.columns>
                    <flux:table.column
                        scope="col"
                        sortable
                        :sorted="$this->sortBy === 'tracking_code'"
                        :direction="$this->sortDirection"
                        wire:click="sort('tracking_code')"
                    >{{ __('Código') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Fila') }}</flux:table.column>
                    <flux:table.column
                        scope="col"
                        sortable
                        :sorted="$this->sortBy === 'site_id'"
                        :direction="$this->sortDirection"
                        wire:click="sort('site_id')"
                    >{{ __('Site') }}</flux:table.column>
                    <flux:table.column
                        scope="col"
                        sortable
                        :sorted="$this->sortBy === 'technician_name'"
                        :direction="$this->sortDirection"
                        wire:click="sort('technician_name')"
                    >{{ __('Técnico') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Relatório') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Check-in') }}</flux:table.column>
                    <flux:table.column
                        scope="col"
                        sortable
                        :sorted="$this->sortBy === 'status'"
                        :direction="$this->sortDirection"
                        wire:click="sort('status')"
                    >{{ __('Status') }}</flux:table.column>
                    <flux:table.column
                        scope="col"
                        sortable
                        :sorted="$this->sortBy === 'created_at'"
                        :direction="$this->sortDirection"
                        wire:click="sort('created_at')"
                    >{{ __('Aberto em') }}</flux:table.column>
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
                            <flux:table.cell>
                                @if ($position = $ticket->queuePosition())
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-zinc-900 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">{{ $position }}</span>
                                @else
                                    <flux:icon name="check" class="size-4 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $ticket->site_id }}</flux:table.cell>
                            <flux:table.cell>{{ $ticket->technician_name }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($ticket->reportTypes->isNotEmpty())
                                    <div class="flex max-w-xs flex-wrap gap-1">
                                        @foreach ($ticket->reportTypes as $reportType)
                                            <flux:badge color="blue" size="sm">{{ $reportType->name }}</flux:badge>
                                        @endforeach
                                    </div>
                                @else
                                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">—</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($ticket->checked_in)
                                    <flux:badge color="emerald" size="sm">{{ __('Feito') }}</flux:badge>
                                @else
                                    <flux:badge size="sm">{{ __('Pendente') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $ticket->statusColor() }}" size="sm">
                                    {{ $ticket->statusLabel() }}
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
                                    :aria-label="__('Ver ticket ') . $ticket->tracking_code"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" align="center">
                                <div class="py-12">
                                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                                        <flux:icon name="magnifying-glass" class="size-5" />
                                    </div>
                                    <flux:heading size="sm" class="mt-3">{{ __('Nenhum ticket encontrado') }}</flux:heading>
                                    <flux:text class="mt-1">{{ __('Nenhum registro corresponde à busca ou aos filtros aplicados.') }}</flux:text>
                                    <div class="mt-4">
                                        <flux:button
                                            variant="subtle"
                                            size="sm"
                                            wire:click="$wire.set('search', ''); $wire.set('status', null)"
                                        >
                                            {{ __('Limpar filtros') }}
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