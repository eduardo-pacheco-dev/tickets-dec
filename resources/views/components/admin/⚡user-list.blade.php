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
    <div class="flex items-center justify-between">
        <flux:heading size="lg">Usuários</flux:heading>
        <div class="flex items-center gap-3">
            <flux:text class="text-sm text-gray-500">{{ $this->counts['total'] }} usuários no total</flux:text>
            <flux:button :href="route('admin.users.create')" variant="primary" icon="plus" wire:navigate>
                Novo Usuário
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <button
            wire:click="$set('role', null)"
            class="rounded-xl border p-4 text-left transition {{ $this->role === null ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Todos</flux:text>
            <div class="mt-1 text-2xl font-bold">{{ $this->counts['total'] }}</div>
        </button>
        <button
            wire:click="$set('role', 'admin')"
            class="rounded-xl border p-4 text-left transition {{ $this->role === 'admin' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Admin</flux:text>
            <div class="mt-1 text-2xl font-bold text-red-600">{{ $this->counts['admin'] }}</div>
        </button>
        <button
            wire:click="$set('role', 'operator')"
            class="rounded-xl border p-4 text-left transition {{ $this->role === 'operator' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Operador</flux:text>
            <div class="mt-1 text-2xl font-bold text-blue-600">{{ $this->counts['operator'] }}</div>
        </button>
        <button
            wire:click="$set('role', 'supervisor')"
            class="rounded-xl border p-4 text-left transition {{ $this->role === 'supervisor' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Supervisor</flux:text>
            <div class="mt-1 text-2xl font-bold text-purple-600">{{ $this->counts['supervisor'] }}</div>
        </button>
        <button
            wire:click="$set('role', 'client')"
            class="rounded-xl border p-4 text-left transition {{ $this->role === 'client' ? 'border-gray-500 bg-gray-50 dark:bg-gray-900/20' : 'border-neutral-200 hover:border-neutral-300 dark:border-neutral-700' }}"
        >
            <flux:text class="text-xs font-medium uppercase text-gray-500">Cliente</flux:text>
            <div class="mt-1 text-2xl font-bold text-gray-600">{{ $this->counts['client'] }}</div>
        </button>
    </div>

    <flux:input
        wire:model.live="search"
        placeholder="Buscar por nome ou email..."
        icon="magnifying-glass"
    />

    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nome</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Criado em</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$user->name" size="sm" />
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="{{ $user->role->color() }}">
                                {{ $user->role->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $user->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                variant="ghost"
                                size="sm"
                                :href="route('admin.users.show', $user)"
                                wire:navigate
                            >
                                Ver detalhes
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center">
                            <flux:text class="py-8 text-gray-500">Nenhum usuário encontrado.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{ $this->users->links() }}
</div>
