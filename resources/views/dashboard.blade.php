<?php

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

$user = auth()->user();
$role = $user->role;

$counts = [
    'total' => Ticket::count(),
    'aberto' => Ticket::where('status', TicketStatus::Aberto)->count(),
    'em_andamento' => Ticket::where('status', TicketStatus::EmAndamento)->count(),
    'resolvido' => Ticket::where('status', TicketStatus::Resolvido)->count(),
];

$recentTickets = Ticket::latest()->take(8)->get();

$pending = Ticket::whereIn('status', [TicketStatus::Aberto, TicketStatus::EmAndamento])
    ->latest()
    ->take(8)
    ->get();

$ticketsToday = Ticket::whereDate('created_at', now()->toDateString())->count();
$totalUsers = User::count();

$stats = [
    [
        'label' => __('Total'),
        'count' => $counts['total'],
        'icon' => 'square-3-stack-3d',
        'href' => route('admin.tickets.index'),
        'tone' => 'neutral',
    ],
    [
        'label' => __('Abertos'),
        'count' => $counts['aberto'],
        'icon' => 'exclamation-triangle',
        'href' => route('admin.tickets.index', ['status' => 'aberto']),
        'tone' => 'amber',
    ],
    [
        'label' => __('Em andamento'),
        'count' => $counts['em_andamento'],
        'icon' => 'bolt',
        'href' => route('admin.tickets.index', ['status' => 'em_andamento']),
        'tone' => 'blue',
    ],
    [
        'label' => __('Resolvidos'),
        'count' => $counts['resolvido'],
        'icon' => 'check-circle',
        'href' => route('admin.tickets.index', ['status' => 'resolvido']),
        'tone' => 'green',
    ],
];

$tones = [
    'neutral' => [
        'icon' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        'value' => 'text-zinc-900 dark:text-white',
        'label' => 'text-zinc-500 dark:text-zinc-400',
    ],
    'amber' => [
        'icon' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
        'value' => 'text-amber-700 dark:text-amber-300',
        'label' => 'text-amber-600/80 dark:text-amber-400/70',
    ],
    'blue' => [
        'icon' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300',
        'value' => 'text-blue-700 dark:text-blue-300',
        'label' => 'text-blue-600/80 dark:text-blue-400/70',
    ],
    'green' => [
        'icon' => 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300',
        'value' => 'text-green-700 dark:text-green-300',
        'label' => 'text-green-600/80 dark:text-green-400/70',
    ],
];

$greeting = now()->hour >= 18
    ? __('Boa noite')
    : (now()->hour >= 12 ? __('Boa tarde') : __('Bom dia'));
?>

<x-layouts::app :title="__('Dashboard')">

    @if ($role->canViewTickets())

        <div class="space-y-6 sm:space-y-8">

            {{-- ═══ Cabeçalho ═══ --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ $greeting }}, {{ $user->name }}</flux:heading>
                    <flux:text class="mt-1 flex items-center gap-1.5">
                        <flux:icon name="calendar-days" class="size-4" />
                        {{ now()->format('d/m/Y') }}
                    </flux:text>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <flux:badge color="{{ $role->color() }}" size="sm">{{ $role->label() }}</flux:badge>
                    <flux:button icon="ticket" :href="route('admin.tickets.index')" wire:navigate>
                        {{ __('Tickets') }}
                    </flux:button>
                </div>
            </div>

            {{-- ═══ Estatísticas ═══ --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3">
                @foreach ($stats as $stat)
                    <a href="{{ $stat['href'] }}" wire:navigate class="transition hover:opacity-90">
                        <flux:card class="flex items-center gap-3 sm:gap-4">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $tones[$stat['tone']]['icon'] }}">
                                <flux:icon name="{{ $stat['icon'] }}" class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-none tabular-nums {{ $tones[$stat['tone']]['value'] }}">
                                    {{ $stat['count'] }}
                                </p>
                                <p class="mt-1 text-xs font-medium {{ $tones[$stat['tone']]['label'] }}">
                                    {{ $stat['label'] }}
                                </p>
                            </div>
                        </flux:card>
                    </a>
                @endforeach

                @if ($role->canManageUsers())
                    <a href="{{ route('admin.users.index') }}" wire:navigate class="transition hover:opacity-90">
                        <flux:card class="flex items-center gap-3 sm:gap-4">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                <flux:icon name="users" class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $totalUsers }}</p>
                                <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Usuários') }}</p>
                            </div>
                        </flux:card>
                    </a>

                    <div>
                        <flux:card class="flex items-center gap-3 sm:gap-4">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                <flux:icon name="calendar" class="size-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-none tabular-nums text-zinc-900 dark:text-white">{{ $ticketsToday }}</p>
                                <p class="mt-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Hoje') }}</p>
                            </div>
                        </flux:card>
                    </div>
                @endif
            </div>

            {{-- ═══ Conteúdo principal ═══ --}}
            <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">

                {{-- Tickets Recentes --}}
                <div class="lg:col-span-2">
                    <flux:card class="overflow-hidden p-0!">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                            <flux:heading>{{ __('Tickets recentes') }}</flux:heading>
                            <a href="{{ route('admin.tickets.index') }}" wire:navigate class="text-sm font-medium text-zinc-500 transition hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                {{ __('Ver todos') }}
                            </a>
                        </div>

                        @if ($recentTickets->isEmpty())
                            <div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
                                <div class="flex size-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700">
                                    <flux:icon name="ticket" class="size-6 text-zinc-400 dark:text-zinc-500" />
                                </div>
                                <flux:text>{{ __('Nenhum ticket ainda.') }}</flux:text>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <flux:table>
                                    <flux:table.columns>
                                        <flux:table.column>{{ __('Código') }}</flux:table.column>
                                        <flux:table.column>{{ __('Site') }}</flux:table.column>
                                        <flux:table.column>{{ __('Técnico') }}</flux:table.column>
                                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                                        <flux:table.column>{{ __('Aberto em') }}</flux:table.column>
                                        <flux:table.column></flux:table.column>
                                    </flux:table.columns>

                                    <flux:table.rows>
                                        @foreach ($recentTickets as $ticket)
                                            <flux:table.row wire:key="recent-{{ $ticket->id }}">
                                                <flux:table.cell>
                                                    <span class="font-mono text-sm font-semibold text-zinc-900 dark:text-white">{{ $ticket->tracking_code }}</span>
                                                </flux:table.cell>
                                                <flux:table.cell>{{ $ticket->site_id }}</flux:table.cell>
                                                <flux:table.cell>{{ $ticket->technician_name }}</flux:table.cell>
                                                <flux:table.cell>
                                                    <flux:badge color="{{ $ticket->status->color() }}" size="sm">{{ $ticket->status->label() }}</flux:badge>
                                                </flux:table.cell>
                                                <flux:table.cell class="text-zinc-500 dark:text-zinc-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                                                <flux:table.cell class="text-right">
                                                    <flux:button
                                                        variant="ghost"
                                                        size="sm"
                                                        icon="chevron-right"
                                                        :href="route('admin.tickets.show', $ticket)"
                                                        wire:navigate
                                                    />
                                                </flux:table.cell>
                                            </flux:table.row>
                                        @endforeach
                                    </flux:table.rows>
                                </flux:table>
                            </div>
                        @endif
                    </flux:card>
                </div>

                {{-- Coluna lateral --}}
                <div class="space-y-6 lg:space-y-8">

                    {{-- Fila Pendente --}}
                    <flux:card class="p-0!">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                            <flux:heading>{{ __('Fila pendente') }}</flux:heading>
                            @if ($pending->isNotEmpty())
                                <flux:badge color="amber" size="sm">{{ $pending->count() }}</flux:badge>
                            @endif
                        </div>

                        @if ($pending->isEmpty())
                            <div class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
                                <div class="flex size-12 items-center justify-center rounded-2xl bg-green-100 dark:bg-green-900/30">
                                    <flux:icon name="check-circle" class="size-6 text-green-600 dark:text-green-400" />
                                </div>
                                <flux:heading size="sm">{{ __('Tudo em dia!') }}</flux:heading>
                                <flux:text>{{ __('Nenhum ticket aguardando.') }}</flux:text>
                            </div>
                        @else
                            <div class="divide-y divide-zinc-100 dark:divide-white/5">
                                @foreach ($pending as $ticket)
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" wire:navigate class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-zinc-50 dark:hover:bg-white/5">
                                        <div class="min-w-0">
                                            <p class="truncate font-mono text-sm font-semibold text-zinc-900 dark:text-white">{{ $ticket->tracking_code }}</p>
                                            <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $ticket->technician_name }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <flux:badge color="{{ $ticket->status->color() }}" size="sm">{{ $ticket->status->label() }}</flux:badge>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </flux:card>

                    {{-- Ações rápidas --}}
                    <flux:card>
                        <flux:heading class="mb-4">{{ __('Ações rápidas') }}</flux:heading>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <flux:button variant="subtle" icon="ticket" :href="route('admin.tickets.index')" wire:navigate>
                                {{ __('Ver tickets') }}
                            </flux:button>
                            @if ($role->canManageUsers())
                                <flux:button variant="subtle" icon="user-plus" :href="route('admin.users.create')" wire:navigate>
                                    {{ __('Novo usuário') }}
                                </flux:button>
                                <flux:button variant="subtle" icon="users" :href="route('admin.users.index')" wire:navigate>
                                    {{ __('Usuários') }}
                                </flux:button>
                            @endif
                            <flux:button variant="subtle" icon="exclamation-triangle" :href="route('admin.tickets.index', ['status' => 'aberto'])" wire:navigate>
                                {{ __('Abertos') }}
                            </flux:button>
                        </div>
                    </flux:card>
                </div>
            </div>
        </div>

    {{-- ═══ CLIENTE ═══ --}}
    @else
        <div class="flex min-h-[70vh] flex-col items-center justify-center py-16 text-center">
            <div class="mx-auto mb-8 flex size-16 items-center justify-center rounded-3xl bg-zinc-900 dark:bg-white">
                <flux:icon name="ticket" class="size-8 text-white dark:text-zinc-900" />
            </div>

            <flux:heading size="lg">{{ $greeting }}, {{ $user->name }}</flux:heading>
            <flux:text class="mx-auto mt-3 max-w-md">
                {{ __('Crie um ticket para reportar um problema ou acompanhe o status de um ticket existente pelo código de acompanhamento.') }}
            </flux:text>

            <div class="mt-10 grid w-full max-w-xl gap-4 sm:grid-cols-2">
                <a href="{{ route('home') }}" wire:navigate class="group">
                    <flux:card class="h-full text-left transition group-hover:border-zinc-300 group-hover:shadow-md dark:group-hover:border-white/20">
                        <flux:icon name="plus" class="mb-4 size-6 text-zinc-500 dark:text-zinc-400" />
                        <flux:heading>{{ __('Abrir Ticket') }}</flux:heading>
                        <flux:text class="mt-1.5">{{ __('Reporte um novo problema para nossa equipe.') }}</flux:text>
                    </flux:card>
                </a>

                <a href="{{ route('tickets.status') }}" wire:navigate class="group">
                    <flux:card class="h-full text-left transition group-hover:border-zinc-300 group-hover:shadow-md dark:group-hover:border-white/20">
                        <flux:icon name="magnifying-glass" class="mb-4 size-6 text-zinc-500 dark:text-zinc-400" />
                        <flux:heading>{{ __('Acompanhar Ticket') }}</flux:heading>
                        <flux:text class="mt-1.5">{{ __('Consulte o status pelo seu código de acompanhamento.') }}</flux:text>
                    </flux:card>
                </a>
            </div>
        </div>
    @endif

</x-layouts::app>