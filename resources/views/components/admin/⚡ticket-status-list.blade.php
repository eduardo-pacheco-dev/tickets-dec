<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $label = '';

    public string $color = 'zinc';

    public int $sort_order = 0;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', 'unique:ticket_statuses,name,'.$this->editingId],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'in:'.implode(',', array_keys(TicketStatus::colorOptions()))],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    #[Computed]
    public function statuses(): \Illuminate\Database\Eloquent\Collection
    {
        return TicketStatus::query()
            ->withCount('tickets')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public function colorOptions(): array
    {
        return TicketStatus::colorOptions();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'label', 'color', 'sort_order']);
        $this->is_active = true;
        $this->color = 'zinc';
        $this->sort_order = (TicketStatus::max('sort_order') ?? -1) + 1;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $status = TicketStatus::findOrFail($id);

        $this->editingId = $status->id;
        $this->name = $status->name;
        $this->label = $status->label;
        $this->color = $status->color;
        $this->sort_order = $status->sort_order;
        $this->is_active = $status->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'label' => $this->label,
            'color' => $this->color,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $status = TicketStatus::findOrFail($this->editingId);

            if ($status->name !== $this->name && $status->tickets()->exists()) {
                $this->addError('name', 'Não é possível alterar o identificador de um status que já está em uso por tickets.');

                return;
            }

            $status->update($data);
        } else {
            TicketStatus::create($data);
        }

        $this->showModal = false;
        $this->dispatch('saved');
    }

    public function toggleActive(int $id): void
    {
        $status = TicketStatus::findOrFail($id);
        $status->update(['is_active' => ! $status->is_active]);
    }

    public function delete(int $id): void
    {
        $status = TicketStatus::findOrFail($id);

        if ($status->tickets()->exists()) {
            return;
        }

        $status->delete();
        $this->dispatch('saved');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">{{ __('Status de Tickets') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Gerencie os status utilizados no acompanhamento dos tickets.') }}</flux:text>
            <flux:text class="mt-2 text-sm font-medium">{{ count($this->statuses) }} {{ __('status cadastrados') }}</flux:text>
        </div>

        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            {{ __('Novo Status') }}
        </flux:button>
    </div>

    @if (empty($this->statuses))
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="flag" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">{{ __('Nenhum status') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Crie o primeiro status para classificar os tickets.') }}</flux:text>
            <div class="mt-5">
                <flux:button wire:click="openCreate" variant="primary" icon="plus">
                    {{ __('Novo Status') }}
                </flux:button>
            </div>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed>
                <flux:table.columns>
                    <flux:table.column scope="col">#</flux:table.column>
                    <flux:table.column scope="col">{{ __('Status') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Cor') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Tickets') }}</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->statuses as $status)
                        <flux:table.row
                            wire:key="ticket-status-{{ $status->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $status->sort_order }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:badge :color="$status->color" size="sm">{{ $status->label }}</flux:badge>
                                    @if (! $status->is_active)
                                        <flux:badge color="zinc" size="sm">{{ __('Inativo') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $status->color }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $status->tickets_count }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        icon="pencil"
                                        wire:click="openEdit({{ $status->id }})"
                                        :aria-label="'Editar ' . $status->label"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        :icon="$status->is_active ? 'eye-slash' : 'eye'"
                                        wire:click="toggleActive({{ $status->id }})"
                                        :aria-label="$status->is_active ? 'Desativar ' . $status->label : 'Ativar ' . $status->label"
                                    />
                                    @if ($status->tickets_count === 0)
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon-only
                                            icon="trash"
                                            wire:click="delete({{ $status->id }})"
                                            wire:confirm="{{ __('Tem certeza que deseja excluir este status?') }}"
                                            :aria-label="'Excluir ' . $status->label"
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
        <flux:modal wire:model="showModal" :data-test="$editingId ? 'edit-ticket-status-modal' : 'create-ticket-status-modal'">
            <flux:heading size="lg">{{ $editingId ? __('Editar') : __('Novo') }} {{ __('Status') }}</flux:heading>

            <form wire:submit="save" class="mt-6 space-y-5">
                <flux:field>
                    <flux:label>{{ __('Identificador (name)') }}</flux:label>
                    <flux:input wire:model="name" placeholder="Ex: em_analise" :disabled="$editingId" />
                    <flux:description>{{ __('Usado internamente. Apenas letras minúsculas, números e underscore.') }}</flux:description>
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Nome exibido') }}</flux:label>
                    <flux:input wire:model="label" placeholder="Ex: Em Análise" />
                    <flux:error name="label" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cor') }}</flux:label>
                    <flux:select wire:model="color">
                        @foreach ($this->colorOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Ordem') }}</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:checkbox wire:model="is_active" label="{{ __('Ativo (aparece na seleção de tickets)') }}" />
                </flux:field>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" variant="subtle" wire:click="$wire.set('showModal', false)">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? __('Salvar') : __('Criar') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>