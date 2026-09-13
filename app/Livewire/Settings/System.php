<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Configurações do sistema')]
class System extends Component
{
    public string $appName = '';

    public function mount(): void
    {
        $this->appName = Setting::get('app.name', config('app.name', 'Laravel'));
    }

    public function save(): void
    {
        $this->validate([
            'appName' => ['required', 'string', 'max:255'],
        ]);

        Setting::set('app.name', trim($this->appName));

        Flux::toast(variant: 'success', text: __('Nome do sistema atualizado.'));
    }
}
