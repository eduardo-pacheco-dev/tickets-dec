<?php

use App\Models\Station;
use App\Models\StationAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

it('redirects guests away from the admin station detail page', function () {
    $station = Station::factory()->create();

    $this->get(route('admin.stations.show', $station))->assertRedirect(route('login'));
});

it('renders the admin station detail page for admin users', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.show', $station))
        ->assertOk()
        ->assertSee($station->site_id)
        ->assertSee($station->city)
        ->assertSee($station->state)
        ->assertSee($station->technology);
});

it('denies non-admin users from the station detail page', function () {
    $user = User::factory()->operator()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.show', $station))->assertForbidden();
});

it('toggles a station active state from the detail page', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    Livewire::test('admin/station-detail', ['station' => $station])
        ->call('toggleActive');

    $this->assertDatabaseHas('stations', [
        'id' => $station->id,
        'is_active' => false,
    ]);
});

it('deletes a station from the detail page', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-detail', ['station' => $station])
        ->call('delete')
        ->assertRedirect(route('admin.stations.index'));

    $this->assertDatabaseMissing('stations', ['id' => $station->id]);
});

it('renders a map section when the station has coordinates', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create(['latitude' => '-10.925094', 'longitude' => '-69.554056']);

    $this->actingAs($user);

    Livewire::test('admin/station-detail', ['station' => $station])
        ->assertSee('Localização no Mapa')
        ->assertSee('openstreetmap.org/export/embed.html')
        ->assertSee('marker=-10.925094%2C-69.554056')
        ->assertSee('Abrir no OpenStreetMap')
        ->assertSee('google.com/maps?q=-10.925094,-69.554056')
        ->assertSee('Abrir no Google Maps');
});

it('renders a placeholder when the station has no coordinates', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create(['latitude' => null, 'longitude' => null]);

    $this->actingAs($user);

    Livewire::test('admin/station-detail', ['station' => $station])
        ->assertSee('Localização no Mapa')
        ->assertDontSee('openstreetmap.org/export/embed.html')
        ->assertSee('não possui coordenadas');
});

it('uploads a TSSR attachment to a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'tssr')
        ->set('attachmentFile', UploadedFile::fake()->create('tssr.pdf', 100, 'application/pdf'))
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('station_attachments', [
        'station_id' => $station->id,
        'type' => 'tssr',
        'original_name' => 'tssr.pdf',
        'uploaded_by' => $user->id,
    ]);

    Storage::disk('public')->assertExists(StationAttachment::first()->path);
});

it('uploads a PPI attachment to a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'ppi')
        ->set('attachmentFile', UploadedFile::fake()->create('ppi.zip', 100, 'application/zip'))
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('station_attachments', [
        'station_id' => $station->id,
        'type' => 'ppi',
        'original_name' => 'ppi.zip',
    ]);
});

it('uploads a DOC-D attachment to a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'doc_d')
        ->set('attachmentFile', UploadedFile::fake()->create('doc-d.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('station_attachments', [
        'station_id' => $station->id,
        'type' => 'doc_d',
        'original_name' => 'doc-d.xlsx',
    ]);
});

it('rejects non excel files for DOC-D attachments', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'doc_d')
        ->set('attachmentFile', UploadedFile::fake()->create('doc-d.pdf', 100, 'application/pdf'))
        ->call('saveAttachment')
        ->assertHasErrors(['attachmentFile']);

    $this->assertDatabaseCount('station_attachments', 0);
});

it('uploads a Nota Fiscal attachment to a station', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'nota_fiscal')
        ->set('attachmentFile', UploadedFile::fake()->create('nota-fiscal.pdf', 100, 'application/pdf'))
        ->call('saveAttachment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('station_attachments', [
        'station_id' => $station->id,
        'type' => 'nota_fiscal',
        'original_name' => 'nota-fiscal.pdf',
    ]);
});

it('rejects non pdf files for Nota Fiscal attachments', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'nota_fiscal')
        ->set('attachmentFile', UploadedFile::fake()->create('nota-fiscal.zip', 100, 'application/zip'))
        ->call('saveAttachment')
        ->assertHasErrors(['attachmentFile']);

    $this->assertDatabaseCount('station_attachments', 0);
});

it('rejects invalid attachment types', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'invalid')
        ->set('attachmentFile', UploadedFile::fake()->create('file.pdf', 100, 'application/pdf'))
        ->call('saveAttachment')
        ->assertHasErrors(['attachmentType']);

    $this->assertDatabaseCount('station_attachments', 0);
});

it('rejects non pdf or zip attachments', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();

    $this->actingAs($user);

    Storage::fake('public');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->set('attachmentType', 'tssr')
        ->set('attachmentFile', UploadedFile::fake()->create('file.png', 100, 'image/png'))
        ->call('saveAttachment')
        ->assertHasErrors(['attachmentFile']);

    $this->assertDatabaseCount('station_attachments', 0);
});

it('lists station attachments', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();
    StationAttachment::factory()->count(3)->create(['station_id' => $station->id]);

    $this->actingAs($user);

    $this->get(route('admin.stations.show', $station))
        ->assertOk()
        ->assertSee('Anexos (TSSR / PPI / DOC-D / Nota Fiscal)');
});

it('deletes a station attachment', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();
    $attachment = StationAttachment::factory()->create(['station_id' => $station->id]);

    $this->actingAs($user);

    Storage::fake('public');

    Storage::disk('public')->put($attachment->path, 'conteúdo');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->call('deleteAttachment', $attachment->id);

    $this->assertDatabaseMissing('station_attachments', ['id' => $attachment->id]);
    Storage::disk('public')->assertMissing($attachment->path);
});

it('downloads a station attachment', function () {
    $user = User::factory()->admin()->create();
    $station = Station::factory()->create();
    $attachment = StationAttachment::factory()->create([
        'station_id' => $station->id,
        'original_name' => 'tssr.pdf',
    ]);

    $this->actingAs($user);

    Storage::fake('public');

    Storage::disk('public')->put($attachment->path, 'conteúdo do arquivo');

    Livewire::test('admin/station-detail', ['station' => $station])
        ->call('downloadAttachment', $attachment->id)
        ->assertFileDownloaded('tssr.pdf');
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

it('searches stations by site id, address id, city, regional or external id', function () {
    $user = User::factory()->admin()->create();
    $bySite = Station::factory()->create(['site_id' => '4G-ABLAJ1']);
    $byCity = Station::factory()->create(['city' => 'ASSIS BRASIL']);
    $byRegional = Station::factory()->create(['regional' => 'NOR']);
    $byExternal = Station::factory()->create(['external_id' => 'ACR999TM']);
    $other = Station::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->set('search', 'ABLAJ1')
        ->assertSee($bySite->site_id)
        ->assertDontSee($byCity->site_id);

    Livewire::test('admin/station-list')
        ->set('search', 'ASSIS BRASIL')
        ->assertSee($byCity->site_id)
        ->assertDontSee($other->site_id);

    Livewire::test('admin/station-list')
        ->set('search', 'NOR')
        ->assertSee($byRegional->site_id);

    Livewire::test('admin/station-list')
        ->set('search', 'ACR999TM')
        ->assertSee($byExternal->site_id);
});

it('filters stations by active status', function () {
    $user = User::factory()->admin()->create();
    $active = Station::factory()->create(['is_active' => true]);
    $inactive = Station::factory()->create(['is_active' => false]);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->set('activeFilter', '1')
        ->assertSee($active->site_id)
        ->assertDontSee($inactive->site_id);

    Livewire::test('admin/station-list')
        ->set('activeFilter', '0')
        ->assertSee($inactive->site_id)
        ->assertDontSee($active->site_id);
});

it('filters stations by state', function () {
    $user = User::factory()->admin()->create();
    $inAcre = Station::factory()->create(['state' => 'AC']);
    $inAmazonas = Station::factory()->create(['state' => 'AM']);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->set('stateFilter', 'AM')
        ->assertSee($inAmazonas->site_id)
        ->assertDontSee($inAcre->site_id);
});

it('filters stations by technology and classification', function () {
    $user = User::factory()->admin()->create();
    $lte = Station::factory()->create(['technology' => 'LTE', 'classification' => 'RANSHARING']);
    $umts = Station::factory()->create(['technology' => 'UMTS', 'classification' => 'ACESSO']);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->set('technologyFilter', 'LTE')
        ->assertSee($lte->site_id)
        ->assertDontSee($umts->site_id);

    Livewire::test('admin/station-list')
        ->set('classificationFilter', 'ACESSO')
        ->assertSee($umts->site_id)
        ->assertDontSee($lte->site_id);
});

it('sorts stations by a column', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->create(['site_id' => '4G-BBBBB', 'technology' => 'LTE']);
    Station::factory()->create(['site_id' => '4G-AAAAA', 'technology' => 'UMTS']);
    Station::factory()->create(['site_id' => '4G-CCCCC', 'technology' => 'GSM']);

    $this->actingAs($user);

    $component = Livewire::test('admin/station-list');
    $component->call('sort', 'technology');
    $first = $component->instance()->stations->first();
    expect($first->technology)->toBe('GSM');

    $component->call('sort', 'technology');
    $first = $component->instance()->stations->first();
    expect($first->technology)->toBe('UMTS');

    $component->call('sort', 'site_id');
    $first = $component->instance()->stations->first();
    expect($first->site_id)->toBe('4G-AAAAA');
});

it('resets filters', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->set('search', 'ABLAJ1')
        ->set('activeFilter', '1')
        ->set('stateFilter', 'AC')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('activeFilter', '')
        ->assertSet('stateFilter', '');
});

it('paginates the station list', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->count(20)->create();

    $this->actingAs($user);

    $component = Livewire::test('admin/station-list');
    expect($component->instance()->stations->count())->toBe(15);
});
