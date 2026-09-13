<?php

use App\Models\Station;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public int $step = 1;

    public string $site_id = '';

    public string $element_type = '';

    public string $technology = '';

    public string $address_id = '';

    public string $classification = '';

    public string $area_holder = '';

    public string $infra_contract_type = '';

    public string $infra_holder = '';

    public string $infra_type = '';

    public string $ev_type = '';

    public string $ev_provider = '';

    public string $observation = '';

    public string $justification = '';

    public string $street_type = '';

    public string $street = '';

    public string $number = '';

    public string $complement = '';

    public string $neighborhood = '';

    public string $city = '';

    public string $state = '';

    public string $cep = '';

    public string $regional = '';

    public string $latitude = '';

    public string $longitude = '';

    public string $status = '';

    public string $tower_type = '';

    public string $aev_nominal = '';

    public string $land_area = '';

    public string $structure_height = '';

    public string $external_id = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'site_id' => ['required', 'string', 'max:255', 'unique:stations,site_id,'.$this->editingId],
            'element_type' => ['required', 'string', 'max:100'],
            'technology' => ['required', 'string', 'max:50'],
            'address_id' => ['required', 'string', 'max:100'],
            'classification' => ['nullable', 'string', 'max:100'],
            'area_holder' => ['nullable', 'string', 'max:150'],
            'infra_contract_type' => ['nullable', 'string', 'max:100'],
            'infra_holder' => ['nullable', 'string', 'max:150'],
            'infra_type' => ['nullable', 'string', 'max:100'],
            'ev_type' => ['nullable', 'string', 'max:100'],
            'ev_provider' => ['nullable', 'string', 'max:150'],
            'observation' => ['nullable', 'string'],
            'justification' => ['nullable', 'string'],
            'street_type' => ['nullable', 'string', 'max:50'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:50'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:150'],
            'state' => ['nullable', 'string', 'max:2'],
            'cep' => ['nullable', 'string', 'max:8'],
            'regional' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'string', 'max:20'],
            'longitude' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:100'],
            'tower_type' => ['nullable', 'string', 'max:100'],
            'aev_nominal' => ['nullable', 'string', 'max:20'],
            'land_area' => ['nullable', 'string', 'max:20'],
            'structure_height' => ['nullable', 'string', 'max:20'],
            'external_id' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    #[Computed]
    public function stations(): \Illuminate\Database\Eloquent\Collection
    {
        return Station::query()->orderBy('site_id')->get();
    }

    public function openCreate(): void
    {
        $this->resetExcept(['showModal']);
        $this->step = 1;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $station = Station::findOrFail($id);

        $this->editingId = $station->id;
        $this->step = 1;
        $this->site_id = $station->site_id;
        $this->element_type = $station->element_type;
        $this->technology = $station->technology;
        $this->address_id = $station->address_id;
        $this->classification = $station->classification ?? '';
        $this->area_holder = $station->area_holder ?? '';
        $this->infra_contract_type = $station->infra_contract_type ?? '';
        $this->infra_holder = $station->infra_holder ?? '';
        $this->infra_type = $station->infra_type ?? '';
        $this->ev_type = $station->ev_type ?? '';
        $this->ev_provider = $station->ev_provider ?? '';
        $this->observation = $station->observation ?? '';
        $this->justification = $station->justification ?? '';
        $this->street_type = $station->street_type ?? '';
        $this->street = $station->street ?? '';
        $this->number = $station->number ?? '';
        $this->complement = $station->complement ?? '';
        $this->neighborhood = $station->neighborhood ?? '';
        $this->city = $station->city ?? '';
        $this->state = $station->state ?? '';
        $this->cep = $station->cep ?? '';
        $this->regional = $station->regional ?? '';
        $this->latitude = $station->latitude ?? '';
        $this->longitude = $station->longitude ?? '';
        $this->status = $station->status ?? '';
        $this->tower_type = $station->tower_type ?? '';
        $this->aev_nominal = $station->aev_nominal ?? '';
        $this->land_area = $station->land_area ?? '';
        $this->structure_height = $station->structure_height ?? '';
        $this->external_id = $station->external_id ?? '';
        $this->is_active = $station->is_active;
        $this->showModal = true;
    }

    public function nextStep(): void
    {
        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'site_id' => strtoupper(trim($this->site_id)),
            'element_type' => strtoupper(trim($this->element_type)),
            'technology' => strtoupper(trim($this->technology)),
            'address_id' => strtoupper(trim($this->address_id)),
            'classification' => $this->nullIfEmpty($this->classification),
            'area_holder' => $this->nullIfEmpty($this->area_holder),
            'infra_contract_type' => $this->nullIfEmpty($this->infra_contract_type),
            'infra_holder' => $this->nullIfEmpty($this->infra_holder),
            'infra_type' => $this->nullIfEmpty($this->infra_type),
            'ev_type' => $this->nullIfEmpty($this->ev_type),
            'ev_provider' => $this->nullIfEmpty($this->ev_provider),
            'observation' => $this->nullIfEmpty($this->observation),
            'justification' => $this->nullIfEmpty($this->justification),
            'street_type' => $this->nullIfEmpty(strtoupper(trim($this->street_type))),
            'street' => $this->nullIfEmpty($this->street),
            'number' => $this->nullIfEmpty($this->number),
            'complement' => $this->nullIfEmpty($this->complement),
            'neighborhood' => $this->nullIfEmpty(strtoupper(trim($this->neighborhood))),
            'city' => $this->nullIfEmpty(strtoupper(trim($this->city))),
            'state' => $this->nullIfEmpty(strtoupper(trim($this->state))),
            'cep' => $this->nullIfEmpty(preg_replace('/\D/', '', $this->cep)),
            'regional' => $this->nullIfEmpty(strtoupper(trim($this->regional))),
            'latitude' => $this->normalizeCoordinate($this->latitude),
            'longitude' => $this->normalizeCoordinate($this->longitude),
            'status' => $this->nullIfEmpty($this->status),
            'tower_type' => $this->nullIfEmpty(strtoupper(trim($this->tower_type))),
            'aev_nominal' => $this->normalizeNumber($this->aev_nominal),
            'land_area' => $this->normalizeNumber($this->land_area),
            'structure_height' => $this->normalizeNumber($this->structure_height),
            'external_id' => $this->nullIfEmpty(strtoupper(trim($this->external_id))),
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Station::findOrFail($this->editingId)->update($data);
        } else {
            Station::create($data);
        }

        $this->showModal = false;
        $this->dispatch('saved');
    }

    public function toggleActive(int $id): void
    {
        $station = Station::findOrFail($id);
        $station->update(['is_active' => ! $station->is_active]);
    }

    public function delete(int $id): void
    {
        Station::findOrFail($id)->delete();
        $this->dispatch('saved');
    }

    private function nullIfEmpty(string $value): ?string
    {
        return trim($value) === '' ? null : trim($value);
    }

    private function normalizeCoordinate(string $value): ?string
    {
        $value = trim($value, " \t\n\r\0\x0B\xC2\xA0");

        if ($value === '') {
            return null;
        }

        return str_replace(',', '.', $value);
    }

    private function normalizeNumber(string $value): ?string
    {
        $value = trim($value, " \t\n\r\0\x0B\xC2\xA0");

        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (string) (float) $value : $value;
    }
};
?>

@php
    $stationSteps = [
        1 => ['label' => 'Identificação', 'description' => 'Site, elemento e tecnologia', 'icon' => 'identification'],
        2 => ['label' => 'Infraestrutura', 'description' => 'Detentores, contrato e tipo', 'icon' => 'building-office'],
        3 => ['label' => 'Endereço', 'description' => 'Localização do site', 'icon' => 'map-pin'],
        4 => ['label' => 'Coordenadas', 'description' => 'Dimensões e observações', 'icon' => 'chart-bar'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">Estações</flux:heading>
            <flux:text class="mt-1">Cadastro de estações de telecomunicações vinculadas aos sites.</flux:text>
            <flux:text class="mt-2 text-sm font-medium">{{ count($this->stations) }} estações cadastradas</flux:text>
        </div>

        <flux:button wire:click="openCreate" variant="primary" icon="plus">
            Nova Estação
        </flux:button>
    </div>

    @if (empty($this->stations))
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="computer-desktop" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">Nenhuma estação</flux:heading>
            <flux:text class="mt-1">Cadastre a primeira estação de telecomunicação.</flux:text>
            <div class="mt-5">
                <flux:button wire:click="openCreate" variant="primary" icon="plus">
                    Nova Estação
                </flux:button>
            </div>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed>
                <flux:table.columns>
                    <flux:table.column scope="col">Site ID</flux:table.column>
                    <flux:table.column scope="col">Endereço ID</flux:table.column>
                    <flux:table.column scope="col">Elemento</flux:table.column>
                    <flux:table.column scope="col">Tecnologia</flux:table.column>
                    <flux:table.column scope="col">Município</flux:table.column>
                    <flux:table.column scope="col">Status</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->stations as $station)
                        <flux:table.row
                            wire:key="station-{{ $station->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell>
                                <span class="font-mono text-sm font-semibold">{{ $station->site_id }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-mono text-sm">{{ $station->address_id ?: '—' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $station->element_type }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $station->technology }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $station->city ?: '—' }}<span class="text-zinc-400">/{{ $station->state ?: '—' }}</span></span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($station->is_active)
                                    <flux:badge color="green" size="sm">Ativa</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Inativa</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        icon="pencil"
                                        wire:click="openEdit({{ $station->id }})"
                                        :aria-label="'Editar ' . $station->site_id"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        :icon="$station->is_active ? 'eye-slash' : 'eye'"
                                        wire:click="toggleActive({{ $station->id }})"
                                        :aria-label="$station->is_active ? 'Desativar ' . $station->site_id : 'Ativar ' . $station->site_id"
                                    />
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon-only
                                        icon="trash"
                                        wire:click="delete({{ $station->id }})"
                                        wire:confirm="Tem certeza que deseja excluir esta estação?"
                                        :aria-label="'Excluir ' . $station->site_id"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif

    @if ($showModal)
        <flux:modal
            wire:model="showModal"
            variant="bare"
            scroll="body"
            :data-test="$editingId ? 'edit-station-modal' : 'create-station-modal'"
        >
            <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-zinc-800 sm:rounded-3xl">
                <div class="flex gap-1 p-2" aria-hidden="true">
                    @for ($i = 1; $i <= 4; $i++)
                        <div
                            class="h-1 flex-1 rounded-full transition-colors duration-300 {{ $step >= $i ? 'bg-accent' : 'bg-zinc-100 dark:bg-white/10' }}"
                        ></div>
                    @endfor
                </div>

                <div class="flex items-start justify-between gap-4 border-b border-zinc-100 px-6 py-5 dark:border-zinc-700/60 sm:px-8 sm:py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                            <flux:icon :name="$editingId ? 'pencil' : 'computer-desktop'" class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg">{{ $editingId ? 'Editar' : 'Nova' }} Estação</flux:heading>
                            <flux:text class="mt-0.5 text-sm">
                                Passo {{ $step }} de 4 · {{ $stationSteps[$step]['label'] }}
                            </flux:text>
                        </div>
                    </div>

                    <flux:modal.close>
                        <flux:button
                            variant="ghost"
                            icon="x-mark"
                            size="sm"
                            aria-label="Fechar"
                            class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!"
                        />
                    </flux:modal.close>
                </div>

                <div class="border-b border-zinc-100 px-6 py-6 dark:border-zinc-700/60 sm:px-8 sm:py-7">
                        <ol class="flex items-center gap-2 sm:gap-3">
                            @foreach ($stationSteps as $stepNumber => $stepMeta)
                                <li class="relative flex min-w-0 flex-1 flex-col items-center gap-1.5 sm:gap-2">
                                    <div class="flex w-full items-center">
                                        <div
                                            class="h-1 flex-1 rounded-full transition-colors duration-300 {{ $stepNumber === 1 ? 'invisible' : '' }} {{ $step > $stepNumber - 1 ? 'bg-emerald-500' : 'bg-zinc-200/80 dark:bg-zinc-700/60' }}"
                                        ></div>

                                        <button
                                            type="button"
                                            @disabled($stepNumber > $step)
                                            wire:click="$wire.set('step', {{ $stepNumber }})"
                                            aria-label="Ir para o passo {{ $stepNumber }}: {{ $stepMeta['label'] }}"
                                            class="relative z-10 flex size-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-semibold transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-40 {{ $step === $stepNumber ? 'border-transparent bg-neutral-900 text-white shadow-md ring-4 ring-neutral-900/15 dark:bg-white dark:text-neutral-900 dark:ring-white/15' : ($step > $stepNumber ? 'border-transparent bg-emerald-500 text-white' : 'border-zinc-200 bg-white text-zinc-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-500') }}"
                                        >
                                            @if ($step > $stepNumber)
                                                <flux:icon name="check" class="size-4" />
                                            @else
                                                {{ $stepNumber }}
                                            @endif
                                        </button>

                                        <div
                                            class="h-1 flex-1 rounded-full transition-colors duration-300 {{ $stepNumber === 4 ? 'invisible' : '' }} {{ $step > $stepNumber ? 'bg-emerald-500' : 'bg-zinc-200/80 dark:bg-zinc-700/60' }}"
                                        ></div>
                                    </div>

                                    <span
                                        class="line-clamp-2 text-center text-[11px] font-semibold leading-tight sm:line-clamp-1 sm:text-xs {{ $step === $stepNumber ? 'text-zinc-900 dark:text-white' : ($step > $stepNumber ? 'text-zinc-600 dark:text-zinc-300' : 'text-zinc-400 dark:text-zinc-500') }}"
                                    >
                                        {{ $stepMeta['label'] }}
                                    </span>
                                    <span class="hidden text-center text-xs leading-snug text-zinc-400 dark:text-zinc-500 md:block">
                                        {{ $stepMeta['description'] }}
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="px-6 py-6 sm:px-8 sm:py-8">
                        <form wire:submit="save">
                            <div
                                wire:key="station-step-{{ $step }}"
                                class="space-y-10 transition-opacity duration-200"
                                wire:loading.class.delay="opacity-50"
                                wire:target="nextStep, previousStep"
                            >
                                @if ($step === 1)
                                    <section>
                                        <div class="flex items-center gap-2">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                                <flux:icon name="identification" class="size-4" />
                                            </div>
                                            <flux:heading size="sm">Identificação</flux:heading>
                                        </div>
                                        <flux:text class="mt-1">Dados que identificam e classificam a estação.</flux:text>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                            <flux:field>
                                                <flux:label>Site ID</flux:label>
                                                <flux:input wire:model="site_id" placeholder="Ex: 4G-ABLAJ1" autofocus />
                                                <flux:error name="site_id" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Endereço ID</flux:label>
                                                <flux:input wire:model="address_id" placeholder="Ex: ACABL_0001" />
                                                <flux:error name="address_id" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tipo de elemento</flux:label>
                                                <flux:select wire:model="element_type" placeholder="Selecione...">
                                                    <flux:select.option value="ENODE B">ENODE B</flux:select.option>
                                                    <flux:select.option value="NODE B">NODE B</flux:select.option>
                                                    <flux:select.option value="BTS">BTS</flux:select.option>
                                                </flux:select>
                                                <flux:error name="element_type" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tecnologia</flux:label>
                                                <flux:select wire:model="technology" placeholder="Selecione...">
                                                    <flux:select.option value="LTE">LTE</flux:select.option>
                                                    <flux:select.option value="UMTS">UMTS</flux:select.option>
                                                    <flux:select.option value="GSM">GSM</flux:select.option>
                                                </flux:select>
                                                <flux:error name="technology" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Classificação</flux:label>
                                                <flux:select wire:model="classification" placeholder="Selecione...">
                                                    <flux:select.option value="RANSHARING">RANSHARING</flux:select.option>
                                                    <flux:select.option value="ACESSO">ACESSO</flux:select.option>
                                                </flux:select>
                                                <flux:error name="classification" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Status</flux:label>
                                                <flux:select wire:model="status" placeholder="Selecione...">
                                                    <flux:select.option value="Aquisitado">Aquisitado</flux:select.option>
                                                    <flux:select.option value="Candidato">Candidato</flux:select.option>
                                                </flux:select>
                                                <flux:error name="status" />
                                            </flux:field>
                                        </div>

                                        <div class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50/60 p-4 dark:border-zinc-700/60 dark:bg-white/[3%]">
                                            <flux:field>
                                                <flux:checkbox wire:model="is_active" label="Ativa (disponível para uso)" />
                                            </flux:field>
                                        </div>
                                    </section>
                                @endif

                                @if ($step === 2)
                                    <section>
                                        <div class="flex items-center gap-2">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                                <flux:icon name="building-office" class="size-4" />
                                            </div>
                                            <flux:heading size="sm">Infraestrutura</flux:heading>
                                        </div>
                                        <flux:text class="mt-1">Detentores, contratos e características da infraestrutura.</flux:text>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                            <flux:field>
                                                <flux:label>Detentor da Área</flux:label>
                                                <flux:input wire:model="area_holder" placeholder="Ex: IHS BRAZIL" />
                                                <flux:error name="area_holder" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tipo de contrato Infra</flux:label>
                                                <flux:select wire:model="infra_contract_type" placeholder="Selecione...">
                                                    <flux:select.option value="Built-to-Suit">Built-to-Suit</flux:select.option>
                                                    <flux:select.option value="Compartilhado">Compartilhado</flux:select.option>
                                                </flux:select>
                                                <flux:error name="infra_contract_type" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Detentor de Infra</flux:label>
                                                <flux:input wire:model="infra_holder" placeholder="Ex: AMERICAN TOWER" />
                                                <flux:error name="infra_holder" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tipo de Infra</flux:label>
                                                <flux:input wire:model="infra_type" placeholder="Ex: Greenfield" />
                                                <flux:error name="infra_type" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tipo de EV</flux:label>
                                                <flux:input wire:model="ev_type" placeholder="Ex: TORRE METALICA TRIANGULAR" />
                                                <flux:error name="ev_type" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Fornecedor de EV</flux:label>
                                                <flux:input wire:model="ev_provider" placeholder="Ex: BRASILSAT" />
                                                <flux:error name="ev_provider" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Tipo da torre</flux:label>
                                                <flux:input wire:model="tower_type" placeholder="Opcional" />
                                                <flux:error name="tower_type" />
                                            </flux:field>
                                        </div>
                                    </section>
                                @endif

                                @if ($step === 3)
                                    <section>
                                        <div class="flex items-center gap-2">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                                <flux:icon name="map-pin" class="size-4" />
                                            </div>
                                            <flux:heading size="sm">Endereço</flux:heading>
                                        </div>
                                        <flux:text class="mt-1">Localização da estação conforme o cadastro do site.</flux:text>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                            <flux:field>
                                                <flux:label>Tipo de logradouro</flux:label>
                                                <flux:select wire:model="street_type" placeholder="Selecione...">
                                                    <flux:select.option value="RUA">RUA</flux:select.option>
                                                    <flux:select.option value="AVENIDA">AVENIDA</flux:select.option>
                                                    <flux:select.option value="RODOVIA">RODOVIA</flux:select.option>
                                                </flux:select>
                                                <flux:error name="street_type" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Logradouro</flux:label>
                                                <flux:input wire:model="street" placeholder="Ex: MANOEL BATISTA DE ARAÚJO" />
                                                <flux:error name="street" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Número</flux:label>
                                                <flux:input wire:model="number" placeholder="Ex: S/N" />
                                                <flux:error name="number" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Complemento</flux:label>
                                                <flux:input wire:model="complement" placeholder="Ex: QUADRA 12, LOTE 09" />
                                                <flux:error name="complement" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Bairro</flux:label>
                                                <flux:input wire:model="neighborhood" placeholder="Ex: CENTRO" />
                                                <flux:error name="neighborhood" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Município</flux:label>
                                                <flux:input wire:model="city" placeholder="Ex: ASSIS BRASIL" />
                                                <flux:error name="city" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Estado</flux:label>
                                                <flux:select wire:model="state" placeholder="Selecione...">
                                                    <flux:select.option value="AC">AC</flux:select.option>
                                                    <flux:select.option value="AL">AL</flux:select.option>
                                                    <flux:select.option value="AM">AM</flux:select.option>
                                                    <flux:select.option value="AP">AP</flux:select.option>
                                                    <flux:select.option value="BA">BA</flux:select.option>
                                                    <flux:select.option value="CE">CE</flux:select.option>
                                                    <flux:select.option value="DF">DF</flux:select.option>
                                                    <flux:select.option value="ES">ES</flux:select.option>
                                                    <flux:select.option value="GO">GO</flux:select.option>
                                                    <flux:select.option value="MA">MA</flux:select.option>
                                                    <flux:select.option value="MG">MG</flux:select.option>
                                                    <flux:select.option value="MS">MS</flux:select.option>
                                                    <flux:select.option value="MT">MT</flux:select.option>
                                                    <flux:select.option value="PA">PA</flux:select.option>
                                                    <flux:select.option value="PB">PB</flux:select.option>
                                                    <flux:select.option value="PE">PE</flux:select.option>
                                                    <flux:select.option value="PI">PI</flux:select.option>
                                                    <flux:select.option value="PR">PR</flux:select.option>
                                                    <flux:select.option value="RJ">RJ</flux:select.option>
                                                    <flux:select.option value="RN">RN</flux:select.option>
                                                    <flux:select.option value="RO">RO</flux:select.option>
                                                    <flux:select.option value="RR">RR</flux:select.option>
                                                    <flux:select.option value="RS">RS</flux:select.option>
                                                    <flux:select.option value="SC">SC</flux:select.option>
                                                    <flux:select.option value="SE">SE</flux:select.option>
                                                    <flux:select.option value="SP">SP</flux:select.option>
                                                    <flux:select.option value="TO">TO</flux:select.option>
                                                </flux:select>
                                                <flux:error name="state" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>CEP</flux:label>
                                                <flux:input wire:model="cep" placeholder="Ex: 69935000" maxlength="8" />
                                                <flux:error name="cep" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Regional</flux:label>
                                                <flux:input wire:model="regional" placeholder="Ex: TCO" />
                                                <flux:error name="regional" />
                                            </flux:field>
                                        </div>
                                    </section>
                                @endif

                                @if ($step === 4)
                                    <section>
                                        <div class="flex items-center gap-2">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                                <flux:icon name="chart-bar" class="size-4" />
                                            </div>
                                            <flux:heading size="sm">Coordenadas e Dimensionamento</flux:heading>
                                        </div>
                                        <flux:text class="mt-1">Dados de geolocalização e medidas da estrutura.</flux:text>

                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                            <flux:field>
                                                <flux:label>Latitude</flux:label>
                                                <flux:input wire:model="latitude" placeholder="Ex: -10,925094" />
                                                <flux:error name="latitude" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Longitude</flux:label>
                                                <flux:input wire:model="longitude" placeholder="Ex: -69,554056" />
                                                <flux:error name="longitude" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>External ID (Station ID)</flux:label>
                                                <flux:input wire:model="external_id" placeholder="Ex: ACR001TM" />
                                                <flux:error name="external_id" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>AEV Nominal</flux:label>
                                                <flux:input wire:model="aev_nominal" placeholder="Ex: 0" />
                                                <flux:error name="aev_nominal" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Área de solo</flux:label>
                                                <flux:input wire:model="land_area" placeholder="Ex: 0" />
                                                <flux:error name="land_area" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Altura da estrutura</flux:label>
                                                <flux:input wire:model="structure_height" placeholder="Ex: 40" />
                                                <flux:error name="structure_height" />
                                            </flux:field>
                                        </div>
                                    </section>

                                    <section>
                                        <div class="flex items-center gap-2">
                                            <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                                                <flux:icon name="document-text" class="size-4" />
                                            </div>
                                            <flux:heading size="sm">Observações</flux:heading>
                                        </div>
                                        <flux:text class="mt-1">Anotações e justificativas sobre a estação.</flux:text>

                                        <div class="mt-4 space-y-4">
                                            <flux:field>
                                                <flux:label>Observação</flux:label>
                                                <flux:textarea wire:model="observation" rows="2" placeholder="Opcional" />
                                                <flux:error name="observation" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Justificativa</flux:label>
                                                <flux:textarea wire:model="justification" rows="2" placeholder="Opcional" />
                                                <flux:error name="justification" />
                                            </flux:field>
                                        </div>
                                    </section>
                                @endif
                            </div>

                            <div class="mt-10 flex items-center justify-between gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-700/60">
                                @if ($step === 1)
                                    <flux:button
                                        type="button"
                                        variant="subtle"
                                        wire:click="$wire.set('showModal', false)"
                                    >
                                        Cancelar
                                    </flux:button>
                                @else
                                    <flux:button
                                        type="button"
                                        variant="subtle"
                                        icon="arrow-left"
                                        wire:click="previousStep"
                                    >
                                        Voltar
                                    </flux:button>
                                @endif

                                @if ($step < 4)
                                    <flux:button type="button" variant="primary" wire:click="nextStep" icon-trailing="arrow-right">
                                        Próximo
                                    </flux:button>
                                @else
                                    <flux:button type="submit" variant="primary" icon="check">
                                        {{ $editingId ? 'Salvar' : 'Criar Estação' }}
                                    </flux:button>
                                @endif
                            </div>
                        </form>
                    </div>
            </div>
        </flux:modal>
    @endif
</div>