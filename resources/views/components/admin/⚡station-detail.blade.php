<?php

use App\Models\Station;
use Illuminate\Support\HtmlString;
use Livewire\Component;

new class extends Component
{
    public Station $station;

    public function mount(Station $station): void
    {
        $this->station = $station;
    }

    public function toggleActive(): void
    {
        $this->station->update(['is_active' => ! $this->station->is_active]);
        $this->station->refresh();
    }

    public function delete(): void
    {
        $this->station->delete();
        $this->redirectRoute('admin.stations.index');
    }

    private function value(?string $value): string
    {
        return trim($value ?? '') === '' ? '—' : $value;
    }

    public function hasCoordinates(): bool
    {
        return is_numeric($this->station->latitude) && is_numeric($this->station->longitude);
    }

    public function mapEmbedUrl(): HtmlString
    {
        $lat = (float) $this->station->latitude;
        $lon = (float) $this->station->longitude;

        $delta = 0.02;

        $minLon = $lon - $delta;
        $minLat = $lat - $delta;
        $maxLon = $lon + $delta;
        $maxLat = $lat + $delta;

        $url = 'https://www.openstreetmap.org/export/embed.html?'
            .http_build_query([
                'bbox' => "{$minLon},{$minLat},{$maxLon},{$maxLat}",
                'layer' => 'mapnik',
                'marker' => "{$lat},{$lon}",
            ]);

        return new HtmlString($url);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('admin.stations.index') }}" wire:navigate>
                Voltar
            </flux:button>
            <flux:heading size="lg">{{ $this->station->site_id }}</flux:heading>
            @if ($this->station->is_active)
                <flux:badge color="green">Ativa</flux:badge>
            @else
                <flux:badge color="zinc">Inativa</flux:badge>
            @endif
            @if ($this->station->status)
                <flux:badge color="blue">{{ $this->station->status }}</flux:badge>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <flux:button
                variant="subtle"
                :icon="$this->station->is_active ? 'eye-slash' : 'eye'"
                wire:click="toggleActive"
            >
                {{ $this->station->is_active ? 'Desativar' : 'Ativar' }}
            </flux:button>

            <flux:button
                variant="danger"
                icon="trash"
                wire:click="delete"
                wire:confirm="Tem certeza que deseja excluir esta estação?"
            >
                Excluir
            </flux:button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="identification" class="size-4" />
                </div>
                <flux:heading size="sm">Identificação</flux:heading>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Site ID</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->station->site_id }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Endereço ID</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->value($this->station->address_id) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tipo de Elemento</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->station->element_type }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tecnologia</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->station->technology }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Classificação</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->classification) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Status</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->status) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">External ID</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->value($this->station->external_id) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Cadastrada em</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->station->created_at->format('d/m/Y H:i') }}</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="building-office" class="size-4" />
                </div>
                <flux:heading size="sm">Infraestrutura</flux:heading>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Detentor da Área</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->area_holder) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tipo de Contrato Infra</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->infra_contract_type) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Detentor de Infra</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->infra_holder) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tipo de Infra</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->infra_type) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tipo de EV</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->ev_type) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Fornecedor de EV</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->ev_provider) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Tipo da Torre</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->tower_type) }}</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="map-pin" class="size-4" />
                </div>
                <flux:heading size="sm">Endereço</flux:heading>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Logradouro</flux:text>
                    <flux:text class="mt-1 font-medium">
                        {{ trim(implode(' ', array_filter([$this->station->street_type, $this->station->street, $this->station->number, $this->station->complement]))) ?: '—' }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Bairro</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->neighborhood) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Município / UF</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->city) }}/{{ $this->value($this->station->state) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">CEP</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->value($this->station->cep) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Regional</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->regional) }}</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="chart-bar" class="size-4" />
                </div>
                <flux:heading size="sm">Coordenadas e Dimensionamento</flux:heading>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Latitude</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->value($this->station->latitude) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Longitude</flux:text>
                    <flux:text class="mt-1 font-mono font-medium">{{ $this->value($this->station->longitude) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">AEV Nominal</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->aev_nominal) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Área de Solo</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->land_area) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase text-gray-500">Altura da Estrutura</flux:text>
                    <flux:text class="mt-1 font-medium">{{ $this->value($this->station->structure_height) }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="map" class="size-4" />
                </div>
                <flux:heading size="sm">Localização no Mapa</flux:heading>
            </div>

            @if ($this->hasCoordinates())
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="arrow-top-right-on-square"
                    :href="'https://www.openstreetmap.org/?mlat=' . (float) $this->station->latitude . '&mlon=' . (float) $this->station->longitude . '#map=17/' . (float) $this->station->latitude . '/' . (float) $this->station->longitude"
                    target="_blank"
                >
                    Abrir no OpenStreetMap
                </flux:button>
            @endif
        </div>

        @if ($this->hasCoordinates())
            <div class="mt-4 overflow-hidden rounded-xl ring-1 ring-zinc-200 dark:ring-zinc-700">
                <iframe
                    src="{{ $this->mapEmbedUrl() }}"
                    title="Mapa da estação {{ $this->station->site_id }}"
                    class="h-[420px] w-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        @else
            <div class="mt-4 flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 py-10 text-center dark:border-zinc-600">
                <flux:icon name="map-pin" class="size-6 text-zinc-400 dark:text-zinc-500" />
                <flux:text class="text-sm">Esta estação não possui coordenadas cadastradas.</flux:text>
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="document-text" class="size-4" />
                </div>
                <flux:heading size="sm">Observação</flux:heading>
            </div>
            <flux:text class="mt-3">{{ $this->value($this->station->observation) }}</flux:text>
        </div>

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-2">
                <div class="flex size-7 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">
                    <flux:icon name="pencil-square" class="size-4" />
                </div>
                <flux:heading size="sm">Justificativa</flux:heading>
            </div>
            <flux:text class="mt-3">{{ $this->value($this->station->justification) }}</flux:text>
        </div>
    </div>
</div>