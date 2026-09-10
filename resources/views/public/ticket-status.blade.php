<x-layouts::public :title="$title">
    <div class="mb-8">
        <flux:heading size="xl">Acompanhar Ticket</flux:heading>
        <flux:text class="mt-2">Informe o código do ticket ou o ID do site (ex.: SITE-0012) para consultar o status.</flux:text>
    </div>

    <form method="GET" action="{{ route('tickets.status') }}" class="mb-8 flex gap-3">
        <flux:input
            name="q"
            :value="$query"
            placeholder="Ex: TK-ABC123 ou SITE-0012"
            class="flex-1"
        />
        <flux:button type="submit" variant="primary">
            <flux:icon name="magnifying-glass" class="size-4" />
            Buscar
        </flux:button>
    </form>

    @php
        $error = match (true) {
            ! $attempted, $query === '' => 'Por favor, insira um código de acompanhamento ou o ID do site.',
            $tickets->isEmpty() => 'Nenhum ticket encontrado para o código ou site informado.',
            default => null,
        };
    @endphp

    @if ($error)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <flux:text class="text-red-700 dark:text-red-400">{{ $error }}</flux:text>
        </div>
    @endif

    @if ($tickets->isNotEmpty())
        <div class="space-y-6">
            @foreach ($tickets as $ticket)
                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">Ticket {{ $ticket->tracking_code }}</flux:heading>
                        <flux:badge color="{{ $ticket->status->color() }}">
                            {{ $ticket->status->label() }}
                        </flux:badge>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <flux:text class="text-xs font-medium uppercase text-gray-500">Site ID</flux:text>
                            <flux:text class="mt-1 font-medium">{{ $ticket->site_id }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium uppercase text-gray-500">Técnico</flux:text>
                            <flux:text class="mt-1 font-medium">{{ $ticket->technician_name }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium uppercase text-gray-500">Check-in</flux:text>
                            <flux:text class="mt-1 font-medium">{{ $ticket->checked_in ? 'Sim' : 'Não' }}</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-xs font-medium uppercase text-gray-500">Aberto em</flux:text>
                            <flux:text class="mt-1 font-medium">{{ $ticket->created_at->format('d/m/Y H:i') }}</flux:text>
                        </div>
                    </div>

                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Relatório(s) Solicitado(s)</flux:text>
                        @if ($ticket->reportTypes->isNotEmpty())
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($ticket->reportTypes as $reportType)
                                    <flux:badge color="blue" size="sm">{{ $reportType->name }}</flux:badge>
                                @endforeach
                            </div>
                        @endif
                        @if ($ticket->report_description)
                            <flux:text class="mt-1">{{ $ticket->report_description }}</flux:text>
                        @endif
                    </div>

                    @if ($ticket->admin_response)
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                            <flux:text class="text-xs font-medium uppercase text-blue-700 dark:text-blue-400">Resposta do Analista</flux:text>
                            <flux:text class="mt-2 text-blue-900 dark:text-blue-200">{{ $ticket->admin_response }}</flux:text>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts::public>