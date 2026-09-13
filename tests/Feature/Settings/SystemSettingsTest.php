<?php

use App\Livewire\Settings\System;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

test('system settings page is displayed for admin users', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $this->get(route('system.edit'))->assertOk();
});

test('system settings page is forbidden for non-admin users', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user);

    $this->get(route('system.edit'))->assertForbidden();
});

test('system name can be updated', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test(System::class)
        ->set('appName', 'Tickets Dec')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('app.name'))->toBe('Tickets Dec');
});

test('system name is required', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test(System::class)
        ->set('appName', '')
        ->call('save')
        ->assertHasErrors(['appName']);

    expect(Setting::get('app.name'))->toBeNull();
});

test('system name is loaded from settings on mount', function () {
    $user = User::factory()->admin()->create();
    Setting::set('app.name', 'Tickets Dec');

    $this->actingAs($user);

    Livewire::test(System::class)
        ->assertSet('appName', 'Tickets Dec');
});
