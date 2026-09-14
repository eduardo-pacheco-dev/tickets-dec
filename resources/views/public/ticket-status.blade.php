<x-layouts::public :title="$title">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Acompanhar Ticket</flux:heading>
            <flux:text class="mt-2">Informe o código do ticket ou o ID do site (ex.: SITE-0012) para consultar o status.</flux:text>
        </div>
        <flux:button :href="route('home')" variant="primary" icon="plus-circle" wire:navigate>
            {{ __('Abrir Ticket') }}
        </flux:button>
    </div>

    <form method="GET" action="{{ route('tickets.status') }}" class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-5">
        <label for="ticket-search" class="mb-2 block text-sm font-medium text-zinc-800 dark:text-zinc-200">
            Buscar ticket
        </label>
        <div class="flex gap-2">
            <flux:input
                id="ticket-search"
                name="q"
                :value="$query"
                icon:leading="magnifying-glass"
                placeholder="Ex: TK-ABC123 ou SITE-0012"
                class="flex-1 self-center"
            />
            <flux:button type="submit" variant="primary" class="self-center">
                Buscar
            </flux:button>
        </div>
        <flux:text class="mt-2 text-sm">Digite o código de acompanhamento, ou o ID do site para ver todos os tickets vinculados a ele.</flux:text>
    </form>

    @php
        $error = match (true) {
            $attempted && $query === '' => 'Por favor, insira um código de acompanhamento ou o ID do site.',
            $attempted && $tickets->isEmpty() => 'Nenhum ticket encontrado para o código ou site informado.',
            default => null,
        };
    @endphp

    @unless ($attempted)
        <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/40">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white shadow-sm dark:bg-zinc-900">
                    <flux:icon name="information-circle" class="size-5 text-zinc-500" />
                </div>
                <div>
                    <flux:heading size="sm">Como acompanhar um ticket?</flux:heading>
                    <flux:text class="mt-1">Digite o código recebido na abertura do ticket (ex.: TK-ABC123) ou o ID do site (ex.: SITE-0012) para ver todos os tickets vinculados.</flux:text>
                </div>
            </div>
        </div>
    @endunless

    @if ($attempted && $query === '')
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20" role="alert">
            <flux:icon name="exclamation-triangle" class="mt-0.5 size-5 shrink-0 text-red-600 dark:text-red-400" />
            <flux:text class="text-red-700 dark:text-red-400">{{ $error }}</flux:text>
        </div>
    @endif

    @if ($attempted && $tickets->isEmpty() && $query !== '')
        <div class="rounded-xl border border-zinc-200 bg-white p-6 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-10">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon name="magnifying-glass" class="size-6 text-zinc-500" />
            </div>
            <flux:heading size="lg" class="mt-4">Nenhum ticket encontrado</flux:heading>
            <flux:text class="mt-1">{{ $error }}</flux:text>
            <flux:text class="mt-2 text-sm text-zinc-500">Verifique se digitou o código corretamente ou tente pelo ID do site (ex.: SITE-0012).</flux:text>
            <div class="mt-6 flex justify-center">
                <flux:button :href="route('home')" variant="primary" icon="plus-circle" wire:navigate>
                    {{ __('Abrir Ticket') }}
                </flux:button>
            </div>
        </div>
    @endif

    @if ($tickets->isNotEmpty())
        <div class="mb-4">
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $tickets->count() === 1 ? '1 ticket encontrado' : $tickets->count().' tickets encontrados' }} para "{{ $query }}"
            </flux:text>
        </div>

        <div class="space-y-6">
            @foreach ($tickets as $ticket)
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 p-5 dark:border-zinc-800 sm:p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <flux:icon name="ticket" class="size-5 text-zinc-500" />
                            </div>
                            <div>
                                <flux:text class="text-xs font-medium uppercase text-zinc-500">Ticket</flux:text>
                                <flux:heading size="lg" class="font-mono">{{ $ticket->tracking_code }}</flux:heading>
                            </div>
                        </div>
                        <flux:badge color="{{ $ticket->statusColor() }}" size="lg">
                            {{ $ticket->statusLabel() }}
                        </flux:badge>
                    </div>

                    <div class="p-5 sm:p-6">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <flux:icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                <div>
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Site ID</dt>
                                    <dd class="mt-0.5 font-medium">{{ $ticket->site_id }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <flux:icon name="user" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                <div>
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Técnico</dt>
                                    <dd class="mt-0.5 font-medium">{{ $ticket->technician_name }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <flux:icon :name="$ticket->checked_in ? 'check-circle' : 'x-circle'" class="mt-0.5 size-4 shrink-0 {{ $ticket->checked_in ? 'text-emerald-500' : 'text-zinc-400' }}" />
                                <div>
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Check-in</dt>
                                    <dd class="mt-0.5 font-medium">{{ $ticket->checked_in ? 'Sim' : 'Não' }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <flux:icon name="calendar-days" class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                <div>
                                    <dt class="text-xs font-medium uppercase text-zinc-500">Aberto em</dt>
                                    <dd class="mt-0.5 font-medium">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            </div>
                        </dl>

                        <div class="mt-5 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <flux:icon name="document-text" class="size-4 shrink-0 text-zinc-400" />
                                <flux:text class="text-xs font-medium uppercase text-zinc-500">Relatório(s) Solicitado(s)</flux:text>
                            </div>
                            @if ($ticket->reportTypes->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($ticket->reportTypes as $reportType)
                                        <flux:badge color="blue" size="sm">{{ $reportType->name }}</flux:badge>
                                    @endforeach
                                </div>
                            @endif
                            @if ($ticket->report_description)
                                <flux:text class="mt-2">{{ $ticket->report_description }}</flux:text>
                            @endif
                        </div>

                        @if ($ticket->admin_response)
                            <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="chat-bubble-left" class="size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                                    <flux:text class="text-xs font-medium uppercase text-blue-700 dark:text-blue-400">Resposta do Analista</flux:text>
                                </div>
                                <flux:text class="mt-2 text-blue-900 dark:text-blue-200">{{ $ticket->admin_response }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts::public>