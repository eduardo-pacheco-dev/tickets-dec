<?php

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public User $user;
    public string $newRole = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->newRole = $user->role->value;
    }

    public function updateRole(): void
    {
        $this->validate([
            'newRole' => ['required', 'string'],
        ]);

        $role = UserRole::from($this->newRole);

        if ($this->user->id === auth()->id()) {
            Flux::toast(variant: 'error', text: 'Você não pode alterar sua própria role.');

            return;
        }

        $this->user->update(['role' => $role]);

        Flux::toast(variant: 'success', text: 'Role atualizada com sucesso.');
    }

    public function deleteUser(): void
    {
        if ($this->user->id === auth()->id()) {
            Flux::toast(variant: 'error', text: 'Você não pode excluir sua própria conta.');

            return;
        }

        $this->user->delete();

        Flux::toast(variant: 'success', text: 'Usuário excluído com sucesso.');

        $this->redirect(route('admin.users.index'), navigate: true);
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('admin.users.index') }}" wire:navigate>
            Voltar
        </flux:button>
        <flux:heading size="lg">Usuário {{ $this->user->name }}</flux:heading>
        <flux:badge color="{{ $this->user->role->color() }}">
            {{ $this->user->role->label() }}
        </flux:badge>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Informações do Usuário</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Nome</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->user->name }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Email</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->user->email }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Email Verificado</flux:text>
                        <flux:text class="mt-1 font-medium">
                            @if ($this->user->email_verified_at)
                                <span class="text-green-600 dark:text-green-400">Verificado em {{ $this->user->email_verified_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="text-gray-400">Não verificado</span>
                            @endif
                        </flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-medium uppercase text-gray-500">Criado em</flux:text>
                        <flux:text class="mt-1 font-medium">{{ $this->user->created_at->format('d/m/Y H:i') }}</flux:text>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Alterar Role</flux:heading>
                <form wire:submit="updateRole" class="space-y-4">
                    <flux:select wire:model="newRole">
                        @foreach (UserRole::cases() as $roleOption)
                            <flux:select.option value="{{ $roleOption->value }}">
                                {{ $roleOption->label() }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="newRole" />
                    <flux:button type="submit" variant="primary" class="w-full">
                        Atualizar Role
                    </flux:button>
                </form>
            </div>

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Ações</flux:heading>
                <div class="space-y-3">
                    <flux:button
                        :href="route('admin.users.edit', $this->user)"
                        variant="ghost"
                        icon="pencil"
                        class="w-full"
                        wire:navigate
                    >
                        Editar Usuário
                    </flux:button>

                    <flux:modal.trigger name="confirm-user-deletion">
                        <flux:button
                            variant="danger"
                            icon="trash"
                            class="w-full"
                        >
                            Excluir Usuário
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Timeline</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 size-2 shrink-0 rounded-full bg-green-500"></div>
                        <div>
                            <flux:text class="text-sm font-medium">Conta criada</flux:text>
                            <flux:text class="text-xs text-gray-500">{{ $this->user->created_at->format('d/m/Y H:i') }}</flux:text>
                        </div>
                    </div>
                    @if ($this->user->updated_at && $this->user->updated_at != $this->user->created_at)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 size-2 shrink-0 rounded-full bg-blue-500"></div>
                            <div>
                                <flux:text class="text-sm font-medium">Última atualização</flux:text>
                                <flux:text class="text-xs text-gray-500">{{ $this->user->updated_at->format('d/m/Y H:i') }}</flux:text>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Tem certeza que deseja excluir este usuário?</flux:heading>
                <flux:subheading>
                    Esta ação não pode ser desfeita. O usuário e todos os seus dados serão permanentemente excluídos.
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">Cancelar</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">Excluir Usuário</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
