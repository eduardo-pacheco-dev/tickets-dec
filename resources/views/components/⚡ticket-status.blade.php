<?php

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public string $tracking_code_input = '';
    public ?Ticket $ticket = null;
    public string $error = '';

    public function search(): void
    {
        $this->error = '';
        $this->ticket = null;

        $code = strtoupper(trim($this->tracking_code_input));

        if ($code === '') {
            $this->error = 'Por favor, insira um código de acompanhamento.';
            return;
        }

        $this->ticket = Ticket::where('tracking_code', $code)->first();

        if ($this->ticket === null) {
            $this->error = 'Ticket não encontrado. Verifique o código e tente novamente.';
        }
    }
};
?>

<div>
    <div class="mb-8">
        <flux:heading size="xl">Acompanhar Ticket</flux:heading>
        <flux:text class="mt-2">Informe o código recebido na abertura do ticket para consultar o status.</flux:text>
    </div>

    <form wire:submit="search" class="mb-8 flex gap-3">
        <flux:input
            wire:model="tracking_code_input"
            placeholder="Ex: TK-ABC123"
            class="flex-1"
        />
        <flux:button type="submit" variant="primary">
            <flux:icon name="magnifying-glass" class="size-4" />
            Buscar
        </flux:button>
    </form>

    @if ($this->error)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <flux:text class="text-red-700 dark:text-red-400">{{ $this->error }}</flux:text>
        </div>
    @endif

    @if ($this->ticket)
        <div class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Ticket {{ $this->ticket->tracking_code }}</flux:heading>
                <flux:badge color="{{ $this->ticket->status->color() }}">
                    {{ $this->ticket->status->label() }}
                </flux:badge>
            </div>

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

            <div>
                <flux:text class="text-xs font-medium uppercase text-gray-500">Relatório Solicitado</flux:text>
                <flux:text class="mt-1">{{ $this->ticket->report_description }}</flux:text>
            </div>

            @if ($this->ticket->admin_response)
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <flux:text class="text-xs font-medium uppercase text-blue-700 dark:text-blue-400">Resposta do Analista</flux:text>
                    <flux:text class="mt-2 text-blue-900 dark:text-blue-200">{{ $this->ticket->admin_response }}</flux:text>
                </div>
            @endif
        </div>
    @endif
</div>
