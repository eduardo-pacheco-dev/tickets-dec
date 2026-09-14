<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Configurações do sistema')]
class System extends Component
{
    public string $appName = '';

    public string $timezone = '';

    public function mount(): void
    {
        $this->appName = Setting::get('app.name', config('app.name', 'Laravel'));
        $this->timezone = Setting::get('app.timezone', config('app.timezone', 'UTC'));
    }

    #[Computed]
    public function timezones(): array
    {
        return collect(timezone_identifiers_list())
            ->mapWithKeys(fn (string $tz) => [$tz => $tz])
            ->all();
    }

    public function save(): void
    {
        $this->validate([
            'appName' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        Setting::set('app.name', trim($this->appName));
        Setting::set('app.timezone', $this->timezone);

        Flux::toast(variant: 'success', text: __('Configurações do sistema atualizadas.'));
    }
}
