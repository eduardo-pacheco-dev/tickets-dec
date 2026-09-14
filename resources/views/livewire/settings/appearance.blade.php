<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-8">
            <div>
                <flux:heading size="sm">{{ __('Theme') }}</flux:heading>
                <flux:text class="mt-1 mb-4">{{ __('Select the appearance for the interface') }}</flux:text>

                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>

            <flux:separator />

            <div>
                <flux:heading size="sm">{{ __('Language') }}</flux:heading>
                <flux:text class="mt-1 mb-4">{{ __('Select the language for the interface') }}</flux:text>

                <livewire:language-selector />
            </div>
        </div>
    </x-settings.layout>
</section>