<?php

use App\Enums\Locale;
use Livewire\Component;

new class extends Component
{
    public string $locale = '';

    public function mount(): void
    {
        $this->locale = auth()->user()?->locale?->value ?? app()->getLocale();
    }

    public function updatedLocale(string $value): void
    {
        if (! in_array($value, array_column(Locale::cases(), 'value'))) {
            return;
        }

        auth()->user()?->update(['locale' => $value]);

        app()->setLocale($value);

        $this->dispatch('locale-changed');
    }

    public function locales(): array
    {
        return array_map(
            fn (Locale $locale) => ['value' => $locale->value, 'label' => $locale->label()],
            Locale::cases()
        );
    }
};
?>

<div>
    <flux:select wire:model.live="locale" data-test="locale-select">
        @foreach ($this->locales() as $option)
            <flux:select.option value="{{ $option['value'] }}">
                {{ $option['label'] }}
            </flux:select.option>
        @endforeach
    </flux:select>
</div>