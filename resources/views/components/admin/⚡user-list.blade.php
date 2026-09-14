<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?string $role = null;

    #[Computed]
    public function counts(): array
    {
        return [
            'total' => User::count(),
            'admin' => User::where('role', UserRole::Admin)->count(),
            'operator' => User::where('role', UserRole::Operator)->count(),
            'supervisor' => User::where('role', UserRole::Supervisor)->count(),
            'client' => User::where('role', UserRole::Client)->count(),
        ];
    }

    #[Computed]
    public function roles(): array
    {
        return UserRole::cases();
    }

    #[Computed]
    public function roleDotColors(): array
    {
        return [
            'admin' => 'bg-red-500',
            'operator' => 'bg-blue-500',
            'supervisor' => 'bg-purple-500',
            'client' => 'bg-zinc-400',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="lg">{{ __('Usuários') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Gerencie as contas do painel e defina o perfil de acesso de cada pessoa.') }}</flux:text>
            <flux:text class="mt-2 text-sm font-medium">{{ $this->counts['total'] }} {{ __('usuários no total') }}</flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($this->role !== null || $this->search !== '')
                <flux:button variant="subtle" size="sm" wire:click="$wire.set('search', ''); $wire.set('role', null)">
                    {{ __('Limpar filtros') }}
                </flux:button>
            @endif

            <flux:button :href="route('admin.users.create')" variant="primary" icon="plus" wire:navigate>
                {{ __('Novo Usuário') }}
            </flux:button>
        </div>
    </div>

    <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div
            role="group"
            aria-label="{{ __('Filtrar por perfil') }}"
            class="flex flex-wrap items-center gap-1 rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700/60 dark:bg-zinc-800/70"
        >
            @php
                $filterButtonClasses = fn ($active) => 'inline-flex h-9 cursor-pointer items-center gap-2 whitespace-nowrap rounded-lg px-3 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-zinc-400 dark:focus-visible:outline-zinc-500 ' . ($active
                    ? 'bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900'
                    : 'text-zinc-500 hover:bg-zinc-200/40 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white');
            @endphp

            <button
                type="button"
                wire:key="filter-all"
                wire:click="$wire.set('role', null)"
                aria-pressed="{{ $this->role === null ? 'true' : 'false' }}"
                class="{{ $filterButtonClasses($this->role === null) }}"
            >
                {{ __('Todos') }}
                <span class="text-xs font-semibold tracking-tight">{{ $this->counts['total'] }}</span>
            </button>

            @foreach ($this->roles as $roleOption)
                <button
                    type="button"
                    wire:key="filter-{{ $roleOption->value }}"
                    wire:click="$wire.set('role', '{{ $roleOption->value }}')"
                    aria-pressed="{{ $this->role === $roleOption->value ? 'true' : 'false' }}"
                    class="{{ $filterButtonClasses($this->role === $roleOption->value) }}"
                >
                    <span class="size-1.5 shrink-0 rounded-full {{ $this->roleDotColors[$roleOption->value] }}"></span>
                    {{ $roleOption->label() }}
                    <span class="text-xs font-semibold tracking-tight">{{ $this->counts[$roleOption->value] }}</span>
                </button>
            @endforeach
        </div>

        <div class="w-full lg:w-80">
            <flux:input
                wire:model.live="search"
                clearable
                icon="magnifying-glass"
                placeholder="{{ __('Buscar por nome ou email...') }}"
            />
        </div>
    </div>

    @if ($this->counts['total'] === 0)
        <flux:card class="py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                <flux:icon name="users" class="size-6" />
            </div>
            <flux:heading size="lg" class="mt-4">{{ __('Nenhum usuário ainda') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Crie a primeira conta para começar a gerenciar o acesso ao painel.') }}</flux:text>
            <div class="mt-5">
                <flux:button :href="route('admin.users.create')" variant="primary" icon="plus" wire:navigate>
                    {{ __('Novo Usuário') }}
                </flux:button>
            </div>
        </flux:card>
    @else
        <flux:card class="overflow-hidden">
            <flux:table bleed :paginate="$this->users">
                <flux:table.columns>
                    <flux:table.column scope="col">{{ __('Usuário') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Email') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Perfil') }}</flux:table.column>
                    <flux:table.column scope="col">{{ __('Criado em') }}</flux:table.column>
                    <flux:table.column scope="col" class="w-px"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->users as $user)
                        <flux:table.row
                            wire:key="user-{{ $user->id }}"
                            class="transition-colors hover:bg-zinc-50 dark:hover:bg-white/[3%]"
                        >
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" size="sm" />
                                    <a
                                        href="{{ route('admin.users.show', $user) }}"
                                        wire:navigate
                                        class="inline-flex items-center gap-2 text-sm font-semibold underline-offset-2 hover:underline"
                                    >
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-xs font-normal text-zinc-400 dark:text-zinc-500">{{ __('(você)') }}</span>
                                        @endif
                                    </a>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $user->email }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ $user->role->color() }}" size="sm">
                                    {{ $user->role->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ $user->created_at->format('H:i') }}</div>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon-only
                                    icon="arrow-right"
                                    :href="route('admin.users.show', $user)"
                                    wire:navigate
                                    :aria-label="__('Ver usuário ') . $user->name"
                                />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" align="center">
                                <div class="py-12">
                                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-400">
                                        <flux:icon name="magnifying-glass" class="size-5" />
                                    </div>
                                    <flux:heading size="sm" class="mt-3">{{ __('Nenhum usuário encontrado') }}</flux:heading>
                                    <flux:text class="mt-1">{{ __('Nenhum registro corresponde à busca ou aos filtros aplicados.') }}</flux:text>
                                    <div class="mt-4">
                                        <flux:button
                                            variant="subtle"
                                            size="sm"
                                            wire:click="$wire.set('search', ''); $wire.set('role', null)"
                                        >
                                            {{ __('Limpar filtros') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endif
</div>