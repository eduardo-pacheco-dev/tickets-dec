<?php

use App\Models\ReportType;
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

        $ticket = Ticket::create([
            'site_id' => $this->site_id,
            'technician_name' => $this->technician_name,
            'report_description' => $this->report_description,
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
            <flux:icon name="check-circle" class="mx-auto size-12 text-green-600 dark:text-green-400" />
            <flux:heading size="lg" class="mt-4">Ticket Criado com Sucesso!</flux:heading>
            <flux:text class="mt-2">Seu código de acompanhamento é:</flux:text>
            <div class="mt-3 inline-block rounded-lg bg-white px-6 py-3 font-mono text-2xl font-bold tracking-wider text-gray-900 shadow dark:bg-zinc-800 dark:text-white">
                {{ $this->tracking_code }}
            </div>
            <flux:text class="mt-4 block text-sm text-gray-500">
                Guarde este código para acompanhar o status do seu ticket.
            </flux:text>
            <flux:button wire:click="$set('tracking_code', null)" variant="primary" class="mt-6">
                Abrir Novo Ticket
            </flux:button>
        </div>
    @else
        <div class="mb-8">
            <flux:heading size="xl">Solicitar Ticket</flux:heading>
            <flux:text class="mt-2">Preencha os dados abaixo para abrir um ticket na fila de avaliação.</flux:text>
        </div>

        <form wire:submit="submit" class="space-y-6">
            <flux:field>
                <flux:label>Site ID</flux:label>
                <flux:input wire:model="site_id" placeholder="Ex: SITE-0012" />
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
                    <flux:text class="mt-1 text-sm">Selecione um ou mais tipos de relatório conforme necessário.</flux:text>
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
    @endif
</div>
