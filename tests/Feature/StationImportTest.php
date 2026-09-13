<?php

use App\Exports\StationsTemplateExport;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

function stationSpreadsheet(array $rows, array $headings = StationsTemplateExport::HEADINGS): UploadedFile
{
    $export = new class($headings, $rows) implements FromArray, WithHeadings
    {
        public function __construct(public array $headings, public array $rows) {}

        public function headings(): array
        {
            return $this->headings;
        }

        public function array(): array
        {
            return $this->rows;
        }
    };

    return UploadedFile::fake()->createWithContent('estacoes.xlsx', Excel::raw($export, 'Xlsx'));
}

it('redirects guests away from the station template download', function () {
    $this->get(route('admin.stations.import-template'))->assertRedirect(route('login'));
});

it('denies non-admin users from downloading the station template', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.import-template'))->assertForbidden();
});

it('denies client users from downloading the station template', function () {
    $user = User::factory()->client()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.import-template'))->assertForbidden();
});

it('allows admins to download the station template', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $this->get(route('admin.stations.import-template'))
        ->assertOk()
        ->assertDownload('modelo-estacoes.xlsx');
});

it('imports stations from an uploaded spreadsheet', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $row = [
        'Site ID' => '4G-ABLAJ1',
        'Endereço ID' => 'ACABL_0001',
        'Tipo de elemento' => 'ENODE B',
        'Tecnologia' => 'LTE',
        'Classificação' => 'RANSHARING',
        'Município' => 'ASSIS BRASIL',
        'Estado' => 'AC',
        'CEP' => '69935-000',
        'Latitude' => '-10,925094',
        'Longitude' => '-69,554056',
    ];

    $file = stationSpreadsheet([array_map(fn (string $heading) => $row[$heading] ?? null, StationsTemplateExport::HEADINGS)]);

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->set('importFile', $file)
        ->call('importStations')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'site_id' => '4G-ABLAJ1',
        'address_id' => 'ACABL_0001',
        'element_type' => 'ENODE B',
        'technology' => 'LTE',
        'classification' => 'RANSHARING',
        'city' => 'ASSIS BRASIL',
        'state' => 'AC',
        'cep' => '69935000',
        'latitude' => '-10.925094',
        'longitude' => '-69.554056',
        'is_active' => true,
    ]);
});

it('imports stations mapping the external id column', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $headings = ['Site ID', 'Endereço ID', 'Tipo de elemento', 'Tecnologia', 'Station ID', 'Município'];
    $file = stationSpreadsheet([
        ['4G-ABLAJ1', 'ACABL_0001', 'ENODE B', 'LTE', 'ACR001TM', 'ASSIS BRASIL'],
    ], $headings);

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->set('importFile', $file)
        ->call('importStations')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stations', [
        'site_id' => '4G-ABLAJ1',
        'external_id' => 'ACR001TM',
    ]);
});

it('updates existing stations sharing the same site id', function () {
    $user = User::factory()->admin()->create();
    Station::factory()->create(['site_id' => '4G-ABLAJ1', 'city' => 'RIO BRANCO']);

    $this->actingAs($user);

    $row = [
        'Site ID' => '4G-ABLAJ1',
        'Endereço ID' => 'ACABL_0001',
        'Tipo de elemento' => 'ENODE B',
        'Tecnologia' => 'LTE',
        'Município' => 'ASSIS BRASIL',
        'Estado' => 'AC',
    ];

    $file = stationSpreadsheet([array_map(fn (string $heading) => $row[$heading] ?? null, StationsTemplateExport::HEADINGS)]);

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->set('importFile', $file)
        ->call('importStations')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('stations', 1);

    $this->assertDatabaseHas('stations', [
        'site_id' => '4G-ABLAJ1',
        'city' => 'ASSIS BRASIL',
        'state' => 'AC',
    ]);
});

it('records an error for rows missing the site id', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $file = stationSpreadsheet([
        array_map(fn (string $heading) => $heading === 'Endereço ID' ? 'ACABL_0001' : null, StationsTemplateExport::HEADINGS),
        array_map(fn (string $heading) => $heading === 'Endereço ID' ? 'ACABL_0002' : null, StationsTemplateExport::HEADINGS),
    ]);

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->set('importFile', $file)
        ->call('importStations')
        ->assertHasNoErrors()
        ->assertSet('importResult.created', 0)
        ->assertSet('importResult.errors', [
            ['row' => 2, 'message' => 'O campo "Site ID" é obrigatório.'],
            ['row' => 3, 'message' => 'O campo "Site ID" é obrigatório.'],
        ]);

    $this->assertDatabaseCount('stations', 0);
});

it('requires an excel file to import stations', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->call('importStations')
        ->assertHasErrors(['importFile']);
});

it('rejects non-excel files when importing stations', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $file = UploadedFile::fake()->createWithContent('estacoes.txt', 'not excel');

    Livewire::test('admin/station-list')
        ->call('openImport')
        ->set('importFile', $file)
        ->call('importStations')
        ->assertHasErrors(['importFile']);
});
