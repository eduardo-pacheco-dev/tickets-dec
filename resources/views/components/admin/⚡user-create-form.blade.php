<?php

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'client';

    public function mount(?int $userId = null): void
    {
        if ($userId) {
            $user = User::findOrFail($userId);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role->value;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string'],
        ];

        if (! $this->userId) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        $role = UserRole::from($this->role);

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $role,
            ]);

            if ($this->password) {
                $user->update(['password' => $this->password]);
            }

            Flux::toast(variant: 'success', text: 'Usuário atualizado com sucesso.');
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => $role,
            ]);

            Flux::toast(variant: 'success', text: 'Usuário criado com sucesso.');
        }

        $this->redirect(route('admin.users.index'), navigate: true);
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" href="{{ route('admin.users.index') }}" wire:navigate>
            Voltar
        </flux:button>
        <flux:heading size="lg">
            {{ $this->userId ? 'Editar Usuário' : 'Novo Usuário' }}
        </flux:heading>
    </div>

    <div class="max-w-xl">
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <flux:heading size="sm" class="mb-4">Dados do Usuário</flux:heading>

                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Nome</flux:label>
                        <flux:input wire:model="name" type="text" placeholder="Nome completo" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input wire:model="email" type="email" placeholder="email@exemplo.com" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Senha {{ $this->userId ? '(deixe vazio para manter)' : '' }}</flux:label>
                        <flux:input wire:model="password" type="password" placeholder="{{ $this->userId ? '••••••••' : 'Mínimo 8 caracteres' }}" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select wire:model="role">
                            @foreach (UserRole::cases() as $roleOption)
                                <flux:select.option value="{{ $roleOption->value }}">
                                    {{ $roleOption->label() }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="role" />
                    </flux:field>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" href="{{ route('admin.users.index') }}" wire:navigate>
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $this->userId ? 'Salvar Alterações' : 'Criar Usuário' }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
