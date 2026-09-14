<?php

use App\Enums\Locale;
use App\Models\User;
use Livewire\Livewire;

test('language selector displays the current locale', function () {
    $user = User::factory()->admin()->create(['locale' => Locale::PtBr]);

    $this->actingAs($user);

    Livewire::test('language-selector')
        ->assertSet('locale', 'pt_BR');
});

test('user can change their interface language', function () {
    $user = User::factory()->admin()->create(['locale' => Locale::PtBr]);

    $this->actingAs($user);

    Livewire::test('language-selector')
        ->set('locale', 'en')
        ->assertSet('locale', 'en');

    expect($user->refresh()->locale)->toBe(Locale::En);
});

test('interface language is applied by the middleware', function () {
    $user = User::factory()->admin()->create(['locale' => Locale::En]);

    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))
        ->assertOk()
        ->assertSee('Track the tickets registered by technicians and manage the service.');
});

test('interface stays in Portuguese when locale is pt_BR', function () {
    $user = User::factory()->admin()->create(['locale' => Locale::PtBr]);

    $this->actingAs($user);

    $this->get(route('admin.tickets.index'))
        ->assertOk()
        ->assertSee('Acompanhe os chamados registrados pelos técnicos e gerencie o atendimento.');
});

test('language selector appears on the appearance settings page', function () {
    $user = User::factory()->admin()->create(['locale' => Locale::PtBr]);

    $this->actingAs($user);

    $this->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('language-selector')
        ->assertSee('pt_BR');
});
