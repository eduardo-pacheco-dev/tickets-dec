<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @props(['title' => null])

        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <x-app-logo href="{{ route('home') }}" wire:navigate />

            <flux:spacer />

            @auth
                <x-desktop-user-menu :name="auth()->user()->name" />
            @else
                <flux:button :href="route('login')" icon="user-circle" wire:navigate>
                    {{ __('Entrar') }}
                </flux:button>
            @endauth
        </flux:header>

        <flux:main class="mx-auto max-w-6xl bg-white dark:bg-zinc-800">
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>