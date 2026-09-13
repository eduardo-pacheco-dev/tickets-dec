<?php

use App\Models\Station;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the admin stations page', function () {
    $this->get(route('admin.stations.index'))->assertRedirect(route('login'));
});

it('renders the admin stations list for admin users', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->count(3)->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.index'))->assertOk();
});

it('denies non-admin users from the stations page', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.index'))->assertForbidden();
});

it('denies client users from the stations page', function () {
    $user = User::factory()->client()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.index'))->assertForbidden();
});

it('creates a station', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openCreate')
        ->set('site_id', '4G-ablaj1')
        ->set('address_id', 'ACABL_0001')
        ->set('element_type', 'ENODE B')
        ->set('technology', 'LTE')
        ->set('classification', 'RANSHARING')
        ->set('city', 'ASSIS BRASIL')
        ->set('state', 'AC')
        ->set('latitude', '-10,925094')
        ->set('longitude', '-69,554056')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'site_id' => '4G-ABLAJ1',
        'address_id' => 'ACABL_0001',
        'element_type' => 'ENODE B',
        'technology' => 'LTE',
        'classification' => 'RANSHARING',
        'city' => 'ASSIS BRASIL',
        'latitude' => '-10.925094',
        'longitude' => '-69.554056',
        'is_active' => true,
    ]);
});

it('validates station fields are required', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openCreate')
        ->call('save')
        ->assertHasErrors(['site_id', 'element_type', 'technology', 'address_id']);

    $this->assertDatabaseCount('stations', 0);
});

it('rejects a duplicate station site id', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openCreate')
        ->set('site_id', '4G-ABLAJ1')
        ->set('address_id', 'ACABL_0001')
        ->set('element_type', 'ENODE B')
        ->set('technology', 'LTE')
        ->call('save')
        ->assertHasErrors(['site_id']);

    $this->assertDatabaseCount('stations', 1);
});

it('normalizes coordinates and numbers correctly', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openCreate')
        ->set('site_id', 'AC10J1')
        ->set('address_id', 'ACACLD0001')
        ->set('element_type', 'NODE B')
        ->set('technology', 'UMTS')
        ->set('latitude', '-10,075556')
        ->set('longitude', '-67,055611')
        ->set('aev_nominal', '0')
        ->set('structure_height', '60')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'site_id' => 'AC10J1',
        'latitude' => '-10.075556',
        'longitude' => '-67.055611',
        'aev_nominal' => '0',
        'structure_height' => '60',
    ]);
});

it('edits a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openEdit', $station->id)
        ->set('city', 'ACRELANDIA')
        ->set('state', 'AC')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'id' => $station->id,
        'city' => 'ACRELANDIA',
    ]);
});

it('allows keeping the same site id when editing a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create(['site_id' => '4G-ABLAJ1']);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openEdit', $station->id)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'id' => $station->id,
        'site_id' => '4G-ABLAJ1',
    ]);
});

it('toggles a station active state', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('toggleActive', $station->id);

    $this->assertDatabaseHas('stations', [
        'id' => $station->id,
        'is_active' => false,
    ]);
});

it('deletes a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('delete', $station->id);

    $this->assertDatabaseMissing('stations', ['id' => $station->id]);
});
