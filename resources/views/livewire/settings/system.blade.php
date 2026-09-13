<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Configurações do sistema') }}</flux:heading>

    <x-settings.layout :heading="__('Configurações do sistema')" :subheading="__('Altere o nome exibido do sistema.')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="appName"
                :label="__('Nome do sistema')"
                type="text"
                required
                autofocus
                autocomplete="off"
                placeholder="Ex: Tickets Dec"
            />
            <flux:error name="appName" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Salvar') }}</flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>