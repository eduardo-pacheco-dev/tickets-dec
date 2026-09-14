<?php

use App\Models\ReportType;
use App\Models\Station;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public'), Title('Solicitar Ticket')] class extends Component
{
    public string $site_id = '';
    public string $technician_name = '';
    public array $report_types = [];
    public string $report_description = '';
    public bool $checked_in = false;
    public ?string $tracking_code = null;

    #[Computed]
    public function reportTypes()
    {
        return ReportType::query()->active()->orderBy('sort_order')->orderBy('name')->get();
    }

    #[Computed]
    public function stationSuggestions(): \Illuminate\Database\Eloquent\Collection
    {
        $query = mb_strtoupper(trim($this->site_id));

        if ($query === '') {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        return Station::query()
            ->active()
            ->where('site_id', 'like', "%{$query}%")
            ->orderBy('site_id')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function queueTickets(): \Illuminate\Database\Eloquent\Collection
    {
        return Ticket::queueTickets();
    }

    public function selectStation(string $siteId): void
    {
        $this->site_id = $siteId;
    }

    protected function rules(): array
    {
        $hasReportTypes = ReportType::query()->active()->exists();

        return [
            'site_id' => ['required', 'string', 'max:255'],
            'technician_name' => ['required', 'string', 'max:255'],
            'report_types' => $hasReportTypes ? ['nullable', 'array', 'max:10'] : ['nullable'],
            'report_types.*' => ['integer', 'distinct', 'exists:report_types,id'],
            'report_description' => $hasReportTypes ? ['nullable', 'string', 'max:2000'] : ['required', 'string', 'max:2000'],
            'checked_in' => ['boolean'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $siteId = mb_strtoupper(trim($this->site_id));
        $technicianName = trim($this->technician_name);
        $reportDescription = trim($this->report_description);

        $ticket = Ticket::create([
            'site_id' => $siteId,
            'technician_name' => $technicianName,
            'report_description' => $reportDescription,
            'checked_in' => $this->checked_in,
        ]);

        if ($this->report_types !== []) {
            $ticket->reportTypes()->sync($this->report_types);
        }

        $recipients = User::query()->ticketStaff()->get();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewTicketNotification($ticket));
        }

        $this->tracking_code = $ticket->tracking_code;

        $this->reset(['site_id', 'technician_name', 'report_types', 'report_description', 'checked_in']);

        $this->dispatch('ticket-created');
    }
};
?>

<div>
    @if ($this->tracking_code)
        <div class="rounded-xl border border-green-200 bg-green-50 p-6 text-center dark:border-green-800 dark:bg-green-900/20">
            <div class="mb-6 space-y-4">
                <flux:icon name="check-circle" class="mx-auto size-12 text-green-600 dark:text-green-400" />
                <div>
                    <flux:heading size="lg">Ticket Criado com Sucesso!</flux:heading>
                    <flux:text class="mt-4 text-sm">Seu código de acompanhamento é:</flux:text>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-4">
                        <div
                            x-data="{ copied: false }"
                            x-on:click="
                                navigator.clipboard.writeText('{{ $this->tracking_code }}');
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="group relative inline-flex cursor-pointer items-center gap-2 rounded-lg bg-white px-6 py-3 font-mono text-2xl font-bold tracking-wider text-gray-900 shadow ring-1 ring-zinc-200 transition hover:bg-zinc-50 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700 dark:hover:bg-zinc-700"
                        >
                            {{ $this->tracking_code }}

                            <span class="text-zinc-400 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300" :class="copied ? 'text-green-600! dark:text-green-400!' : ''">
                                <span x-show="!copied">
                                    <flux:icon name="clipboard" class="size-5" />
                                </span>
                                <span x-show="copied" x-cloak>
                                    <flux:icon name="check" class="size-5" />
                                </span>
                            </span>

                            <span
                                x-show="copied"
                                x-cloak
                                class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-zinc-900 px-2 py-0.5 text-xs font-medium text-white shadow dark:bg-white dark:text-zinc-900"
                            >Copiado!</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <flux:button
                        variant="primary"
                        icon="magnifying-glass"
                        :href="route('tickets.status', ['q' => $this->tracking_code])"
                        wire:navigate
                    >
                        Acompanhar Ticket
                    </flux:button>

                    <flux:button wire:click="$wire.set('tracking_code', null)" variant="ghost" size="sm">
                        Abrir Novo Ticket
                    </flux:button>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-[1fr_360px]">
            <div>
                <div class="mb-6">
                    <flux:heading size="xl">Solicitar Ticket</flux:heading>
                    <flux:text class="mt-2">Preencha os dados abaixo para abrir um ticket na fila de avaliação.</flux:text>
                </div>

                <div class="mb-6">
                    <flux:button
                        :href="route('tickets.status')"
                        variant="outline"
                        icon="magnifying-glass"
                        class="w-full !h-12 !text-base"
                        wire:navigate
                    >
                        {{ __('Acompanhar Ticket') }}
                    </flux:button>
                </div>

        <form wire:submit="submit" id="ticket-form" x-ref="form" class="space-y-6 border-t border-zinc-200 pt-6 scroll-mt-24 dark:border-zinc-700">
            <flux:field>
                <flux:label>Site ID</flux:label>
                <div class="relative" x-data="{ open: false, focusIndex: -1 }">
                    <flux:input
                        wire:model.live="site_id"
                        placeholder="Ex: SITE-0012"
                        autocomplete="off"
                        icon:trailing="chevron-down"
                        x-ref="input"
                        @input="open = true"
                        @focus="open = $wire.stationSuggestions.length > 0"
                        @keydown.down.prevent="open = true; focusIndex = Math.min(focusIndex + 1, $wire.stationSuggestions.length - 1)"
                        @keydown.up.prevent="focusIndex = Math.max(focusIndex - 1, 0)"
                        @keydown.enter.prevent="
                            if (focusIndex >= 0 && $wire.stationSuggestions[focusIndex]) {
                                $wire.selectStation($wire.stationSuggestions[focusIndex].site_id);
                                open = false;
                            } else {
                                $refs.form.requestSubmit();
                            }
                        "
                        @click.outside="open = false"
                    />

                    @if ($this->stationSuggestions->isNotEmpty())
                        <div
                            x-show="open"
                            x-cloak
                            class="absolute z-10 mt-1 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            <ul class="max-h-60 overflow-y-auto py-1">
                                @foreach ($this->stationSuggestions as $station)
                                    <li>
                                        <button
                                            type="button"
                                            wire:key="station-suggestion-{{ $station->id }}"
                                            wire:click="selectStation('{{ $station->site_id }}')"
                                            @click="open = false"
                                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm transition hover:bg-zinc-50 dark:hover:bg-white/10"
                                        >
                                            <span class="font-mono font-medium">{{ $station->site_id }}</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $station->city ? $station->city.'/'.$station->state : '' }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <flux:description>Digite o código do site para buscar entre as estações cadastradas.</flux:description>
                <flux:error name="site_id" />
            </flux:field>

            <flux:field>
                <flux:label>Nome do Técnico</flux:label>
                <flux:input wire:model="technician_name" placeholder="Seu nome completo" />
                <flux:error name="technician_name" />
            </flux:field>

            @if ($this->reportTypes->isNotEmpty())
                <flux:field>
                    <flux:label>Relatórios Solicitados</flux:label>
<div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($this->reportTypes as $reportType)
                            <flux:checkbox
                                variant="cards"
                                wire:model="report_types"
                                value="{{ $reportType->id }}"
                                :label="$reportType->name"
                                wire:key="report-type-{{ $reportType->id }}"
                            />
                        @endforeach
                    </div>
                    <flux:text class="mt-1.5 text-sm">Selecione um ou mais tipos de relatório conforme necessário.</flux:text>
                    <flux:error name="report_types" />
                </flux:field>
            @else
                <flux:field>
                    <flux:label>Relatório Solicitado</flux:label>
                    <flux:textarea wire:model="report_description" rows="4" placeholder="Descreva a situação ou o que precisa ser avaliado..." />
                    <flux:error name="report_description" />
                </flux:field>
            @endif

            <flux:field>
                <flux:checkbox wire:model="checked_in" label="Check-in realizado no site" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full">
                Abrir Ticket
            </flux:button>
        </form>
            </div>

            <aside class="lg:sticky lg:top-6">
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <flux:icon name="queue-list" class="size-4 text-zinc-400" />
                            <flux:heading size="sm">Fila de Avaliação</flux:heading>
                        </div>
                        <flux:badge color="zinc" size="sm">{{ $this->queueTickets->count() }}</flux:badge>
                    </div>

                    <div class="max-h-[70vh] overflow-y-auto">
                        @if ($this->queueTickets->isEmpty())
                            <div class="flex flex-col items-center justify-center gap-2 px-4 py-10 text-center">
                                <flux:icon name="check-circle" class="size-6 text-green-500" />
                                <flux:text class="text-sm">A fila está vazia.</flux:text>
                            </div>
                        @else
                            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($this->queueTickets as $ticket)
                                    <li class="flex items-center gap-3 px-4 py-3">
                                        <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-xs font-bold text-white dark:bg-white dark:text-zinc-900">
                                            {{ $ticket->queue_number }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-mono text-sm font-semibold">{{ $ticket->tracking_code }}</p>
                                            <p class="truncate text-xs text-zinc-400">{{ $ticket->site_id }}</p>
                                        </div>
                                        <flux:badge :color="$ticket->statusColor()" size="sm">{{ $ticket->statusLabel() }}</flux:badge>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
