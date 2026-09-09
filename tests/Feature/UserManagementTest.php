<?php

use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders the user list page', function () {
    $response = $this->get(route('admin.users.index'));
    $response->assertOk();
});

it('lists users', function () {
    User::factory()->count(3)->create();

    Livewire::test('admin/user-list')
        ->assertSee($this->admin->name)
        ->assertSee('usuários no total');
});

it('searches users by name', function () {
    User::factory()->create(['name' => 'João Buscado']);
    User::factory()->create(['name' => 'Maria Silva']);

    Livewire::test('admin/user-list')
        ->set('search', 'João')
        ->assertSee('João Buscado')
        ->assertDontSee('Maria Silva');
});

it('searches users by email', function () {
    User::factory()->create(['email' => 'joao@example.com']);
    User::factory()->create(['email' => 'maria@example.com']);

    Livewire::test('admin/user-list')
        ->set('search', 'joao')
        ->assertSee('joao@example.com')
        ->assertDontSee('maria@example.com');
});

it('filters users by role', function () {
    User::factory()->admin()->create(['name' => 'Admin User']);
    User::factory()->operator()->create(['name' => 'Operator User']);
    User::factory()->client()->create(['name' => 'Client User']);

    Livewire::test('admin/user-list')
        ->set('role', 'admin')
        ->assertSee('Admin User')
        ->assertDontSee('Operator User')
        ->assertDontSee('Client User');
});

it('paginates the user list', function () {
    User::factory()->count(20)->create();

    $component = Livewire::test('admin/user-list');
    expect($component->instance()->users->count())->toBe(15);
});

it('renders the create user form', function () {
    $response = $this->get(route('admin.users.create'));
    $response->assertOk();
});

it('creates a new user', function () {
    Livewire::test('admin/user-create-form')
        ->set('name', 'Novo Usuário')
        ->set('email', 'novo@example.com')
        ->set('password', 'password123')
        ->set('role', 'operator')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'Novo Usuário',
        'email' => 'novo@example.com',
        'role' => 'operator',
    ]);
});

it('validates required fields when creating a user', function () {
    Livewire::test('admin/user-create-form')
        ->set('role', '')
        ->call('save')
        ->assertHasErrors(['name', 'email', 'password', 'role']);
});

it('validates email format when creating a user', function () {
    Livewire::test('admin/user-create-form')
        ->set('name', 'Test')
        ->set('email', 'invalid-email')
        ->set('password', 'password123')
        ->set('role', 'client')
        ->call('save')
        ->assertHasErrors(['email']);
});

it('validates minimum password length when creating a user', function () {
    Livewire::test('admin/user-create-form')
        ->set('name', 'Test')
        ->set('email', 'test@example.com')
        ->set('password', 'short')
        ->set('role', 'client')
        ->call('save')
        ->assertHasErrors(['password']);
});

it('renders the user detail page', function () {
    $user = User::factory()->create();
    $response = $this->get(route('admin.users.show', $user));
    $response->assertOk();
});

it('shows user information on detail page', function () {
    $user = User::factory()->create([
        'name' => 'Detalhes Teste',
        'email' => 'detalhes@example.com',
        'role' => 'supervisor',
    ]);

    Livewire::test('admin/user-detail', ['user' => $user])
        ->assertSee('Detalhes Teste')
        ->assertSee('detalhes@example.com')
        ->assertSee('Supervisor');
});

it('updates user role', function () {
    $user = User::factory()->client()->create();

    Livewire::test('admin/user-detail', ['user' => $user])
        ->set('newRole', 'operator')
        ->call('updateRole')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'operator',
    ]);
});

it('prevents admin from changing their own role', function () {
    Livewire::test('admin/user-detail', ['user' => $this->admin])
        ->set('newRole', 'client')
        ->call('updateRole');

    $this->assertDatabaseHas('users', [
        'id' => $this->admin->id,
        'role' => 'admin',
    ]);
});

it('deletes a user', function () {
    $user = User::factory()->create();

    Livewire::test('admin/user-detail', ['user' => $user])
        ->call('deleteUser');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('prevents admin from deleting themselves', function () {
    Livewire::test('admin/user-detail', ['user' => $this->admin])
        ->call('deleteUser');

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});

it('renders the edit user form', function () {
    $user = User::factory()->create();
    $response = $this->get(route('admin.users.edit', $user));
    $response->assertOk();
});

it('pre-fills edit form with user data', function () {
    $user = User::factory()->create([
        'name' => 'Edit Teste',
        'email' => 'edit@example.com',
        'role' => 'operator',
    ]);

    Livewire::test('admin/user-edit-form', ['userId' => $user->id])
        ->assertSet('name', 'Edit Teste')
        ->assertSet('email', 'edit@example.com')
        ->assertSet('role', 'operator');
});

it('updates an existing user', function () {
    $user = User::factory()->create();

    Livewire::test('admin/user-edit-form', ['userId' => $user->id])
        ->set('name', 'Nome Atualizado')
        ->set('email', 'atualizado@example.com')
        ->set('role', 'supervisor')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nome Atualizado',
        'email' => 'atualizado@example.com',
        'role' => 'supervisor',
    ]);
});

it('does not require password when editing a user', function () {
    $user = User::factory()->create(['password' => 'original-password']);

    Livewire::test('admin/user-edit-form', ['userId' => $user->id])
        ->set('name', 'Atualizado')
        ->set('email', $user->email)
        ->set('role', 'admin')
        ->set('password', '')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Atualizado',
    ]);
});
