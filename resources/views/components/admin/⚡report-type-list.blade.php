<?php

use App\Models\ReportType;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    #[Computed]
    public function reportTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return ReportType::query()->withCount('tickets')->orderBy('sort_order')->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description', 'sort_order']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $reportType = ReportType::findOrFail($id);

        $this->editingId = $reportType->id;
        $this->name = $reportType->name;
        $this->description = $reportType->description ?? '';
        $this->is_active = $reportType->is_active;
        $this->sort_order = $reportType->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            ReportType::findOrFail($this->editingId)->update($data);
        } else {
            ReportType::create($data);
        }

        $this->showModal = false;
        $this->dispatch('saved');
    }

    public function toggleActive(int $id): void
    {
        $reportType = ReportType::findOrFail($id);
        $reportType->update(['is_active' => ! $reportType->is_active]);
    }

    public function delete(int $id): void
    {
        $reportType = ReportType::findOrFail($id);

        if ($reportType->tickets()->exists()) {
            return;
        }

        $reportType->delete();
        $this->dispatch('saved');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">Relatórios</flux:heading>
            <flux:text class="mt-1">Gerencie os tipos de relatório disponíveis para abertura de tickets.</flux:text>
            <flux:text class="mt-2 text-sm font-medium">{{ count($this->reportTypes) }} tipos cadastrados</flux:text>
        </div>

        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Novo Tipo
        </flux:button>
    </div>

    @if (empty($this->reportTypes))
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="document-text" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">Nenhum tipo de relatório</flux:heading>
            <flux:text class="mt-1">Crie o primeiro tipo para que os técnicos possam selecioná-lo ao abrir tickets.</flux:text>
            <div class="mt-5">
                <flux:button wire:click="openCreate" variant="primary" icon="plus">
                    Novo Tipo
                </flux:button>
            </div>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed>
                <flux:table.columns>
                    <flux:table.column scope="col">Nome</flux:table.column>
                    <flux:table.column scope="col">Descrição</flux:table.column>
                    <flux:table.column scope="col">Status</flux:table.column>
                    <flux:table.column scope="col">Tickets</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->reportTypes as $reportType)
                        <flux:table.row
                            wire:key="report-type-{{ $reportType->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell>
                                <span class="text-sm font-semibold">{{ $reportType->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $reportType->description ?: '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($reportType->is_active)
                                    <flux:badge color="green" size="sm">Ativo</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Inativo</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $reportType->tickets_count }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        icon="pencil"
                                        wire:click="openEdit({{ $reportType->id }})"
                                        aria-label="Editar {{ $reportType->name }}"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        :icon="$reportType->is_active ? 'eye-slash' : 'eye'"
                                        wire:click="toggleActive({{ $reportType->id }})"
                                        :aria-label="$reportType->is_active ? 'Desativar ' . $reportType->name : 'Ativar ' . $reportType->name"
                                    />
                                    @if ($reportType->tickets_count === 0)
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon-only
                                            icon="trash"
                                            wire:click="delete({{ $reportType->id }})"
                                            wire:confirm="Tem certeza que deseja excluir este tipo?"
                                            :aria-label="'Excluir ' . $reportType->name"
                                        />
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    @if ($showModal)
        <flux:modal wire:model="showModal" :data-test="$editingId ? 'edit-report-type-modal' : 'create-report-type-modal'">
            <flux:heading size="lg">{{ $editingId ? 'Editar' : 'Novo' }} Tipo de Relatório</flux:heading>

            <form wire:submit="save" class="mt-6 space-y-5">
                <flux:field>
                    <flux:label>Nome</flux:label>
                    <flux:input wire:model="name" placeholder="Ex: Vistoria Elétrica" autofocus />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Descrição</flux:label>
                    <flux:textarea wire:model="description" rows="2" placeholder="Opcional. Descreva brevemente este tipo de relatório." />
                    <flux:error name="description" />
                </flux:field>

                <flux:field>
                    <flux:label>Ordem</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="Ativo (aparece na seleção de tickets)" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" variant="subtle" wire:click="$wire.set('showModal', false)">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Salvar' : 'Criar' }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
