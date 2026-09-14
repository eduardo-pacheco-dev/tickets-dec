<?php

use App\Models\Station;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;

it('renders the public ticket creation form', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
    $response->assertSee('Solicitar Ticket - '.config('app.name'), false);
});

it('redirects the old abrir-ticket url to the root', function () {
    $response = $this->get('/abrir-ticket');
    $response->assertRedirect('/');
});

it('does not render the admin sidebar layout on the ticket form', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
    $response->assertSee('Solicitar Ticket');
    $response->assertDontSee('Administração');
});

it('shows prominent ticket action buttons on the home page', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Acompanhar Ticket')
        ->assertSee(route('tickets.status'));
});

it('creates a ticket with valid data', function () {
    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Cliente relatou queda de conexão.')
        ->set('checked_in', true)
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('tickets', 1);
    $this->assertDatabaseHas('tickets', [
        'site_id' => 'SITE-001',
        'technician_name' => 'João Silva',
        'report_description' => 'Cliente relatou queda de conexão.',
        'checked_in' => true,
        'status' => 'aberto',
    ]);
});

it('requires all mandatory fields', function () {
    Livewire::test('ticket-form')
        ->call('submit')
        ->assertHasErrors(['site_id', 'technician_name', 'report_description']);

    $this->assertDatabaseCount('tickets', 0);
});

it('generates a unique tracking code for the ticket', function () {
    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Teste de relatório.')
        ->call('submit')
        ->assertHasNoErrors();

    $ticket = Ticket::first();
    expect($ticket->tracking_code)->toMatch('/^TK-[A-Z0-9]{6}$/');
});

it('does not generate duplicate tracking codes', function () {
    Ticket::factory()->create(['tracking_code' => 'TK-ABC123']);

    expect(Ticket::generateTrackingCode())->not->toBe('TK-ABC123');
});

it('sets a default status of aberto when no status is provided', function () {
    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Teste de relatório.')
        ->call('submit');

    $ticket = Ticket::first();
    expect($ticket->status)->toBe('aberto');
});

it('shows copy and tracking buttons after creating a ticket', function () {
    $component = Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Teste de relatório.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('Ticket Criado com Sucesso!')
        ->assertSee('Copiado!')
        ->assertSee('Acompanhar Ticket')
        ->assertSee('Abrir Novo Ticket');

    $code = $component->instance()->tracking_code;

    $component->assertSee(route('tickets.status', ['q' => $code]))
        ->assertSee('writeText');
});

it('offers registered stations as suggestions for the site id', function () {
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);
    Station::factory()->create(['site_id' => '4G-HU6713']);

    Livewire::test('ticket-form')
        ->set('site_id', '4G-')
        ->assertSee('4G-ABLAJ1')
        ->assertSee('4G-HU6713');
});

it('filters station suggestions as the user types', function () {
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);
    Station::factory()->create(['site_id' => '4G-HU6713']);

    Livewire::test('ticket-form')
        ->set('site_id', 'ABLA')
        ->assertSee('4G-ABLAJ1')
        ->assertDontSee('4G-HU6713');
});

it('selects a station from the suggestions', function () {
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);

    Livewire::test('ticket-form')
        ->set('site_id', '4G-')
        ->call('selectStation', '4G-ABLAJ1')
        ->assertSet('site_id', '4G-ABLAJ1');
});

it('shows no suggestions when the site id field is empty', function () {
    Station::factory()->create(['site_id' => '4G-ABLAJ1']);

    Livewire::test('ticket-form')
        ->assertSet('stationSuggestions', new Collection);
});

it('creates a ticket with an unregistered site id', function () {
    Livewire::test('ticket-form')
        ->set('site_id', '4G-NOVO123')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Site não cadastrado.')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', ['site_id' => '4G-NOVO123']);
});

it('sanitizes and normalizes the submitted values', function () {
    Livewire::test('ticket-form')
        ->set('site_id', '  site-001  ')
        ->set('technician_name', '  João Silva  ')
        ->set('report_description', '  Teste com espaços.  ')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'site_id' => 'SITE-001',
        'technician_name' => 'João Silva',
        'report_description' => 'Teste com espaços.',
    ]);
});

it('shows the evaluation queue on the home page', function () {
    TicketStatus::factory()->create(['name' => 'aberto', 'label' => 'Aberto', 'sort_order' => 1]);
    TicketStatus::factory()->create(['name' => 'resolvido', 'label' => 'Resolvido', 'sort_order' => 2]);

    $queued = Ticket::factory()->create(['status' => 'aberto', 'site_id' => 'SITE-QUEUE1']);
    Ticket::factory()->create(['status' => 'resolvido', 'site_id' => 'SITE-DONE1']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Fila de Avaliação')
        ->assertSee($queued->tracking_code)
        ->assertSee('SITE-QUEUE1')
        ->assertDontSee('SITE-DONE1');
});

it('shows the queue position on the home page', function () {
    TicketStatus::factory()->create(['name' => 'aberto', 'label' => 'Aberto', 'sort_order' => 1]);
    TicketStatus::factory()->create(['name' => 'resolvido', 'label' => 'Resolvido', 'sort_order' => 2]);

    $first = Ticket::factory()->create(['status' => 'aberto', 'created_at' => now()->subHours(2)]);
    $second = Ticket::factory()->create(['status' => 'aberto', 'created_at' => now()->subHours(1)]);

    expect($first->queuePosition())->toBe(1);
    expect($second->queuePosition())->toBe(2);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($first->tracking_code)
        ->assertSee($second->tracking_code);
});
