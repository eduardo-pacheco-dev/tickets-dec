<?php

use App\Models\Ticket;

it('renders the public ticket status page', function () {
    $response = $this->get(route('tickets.status'));

    $response->assertOk();
    $response->assertSee('Acompanhar Ticket - '.config('app.name'), false);
});

it('does not render the admin sidebar layout on the status page', function () {
    $response = $this->get(route('tickets.status'));

    $response->assertOk();
    $response->assertSee('Acompanhar Ticket');
    $response->assertDontSee('Administração');
});

it('does not require a livewire endpoint for the search', function () {
    $ticket = Ticket::factory()->create();

    $this->get(route('tickets.status', ['q' => $ticket->tracking_code]))
        ->assertOk();
});

it('displays the ticket status when a valid code is provided', function () {
    $ticket = Ticket::factory()->create();

    $this->get(route('tickets.status', ['q' => $ticket->tracking_code]))
        ->assertOk()
        ->assertSee($ticket->tracking_code);
});

it('finds tickets by site id', function () {
    $ticket = Ticket::factory()->create(['site_id' => 'SITE-789']);

    $this->get(route('tickets.status', ['q' => 'SITE-789']))
        ->assertOk()
        ->assertSee($ticket->tracking_code);
});

it('finds tickets by a partial site id', function () {
    $ticket = Ticket::factory()->create(['site_id' => 'SITE-789']);

    $this->get(route('tickets.status', ['q' => 'SITE-7']))
        ->assertOk()
        ->assertSee($ticket->tracking_code);
});

it('lists all tickets for a matching site', function () {
    $older = Ticket::factory()->create([
        'site_id' => 'SITE-789',
        'created_at' => now()->subDay(),
    ]);
    $newer = Ticket::factory()->create(['site_id' => 'SITE-789']);

    $this->get(route('tickets.status', ['q' => 'SITE-789']))
        ->assertOk()
        ->assertSee($newer->tracking_code)
        ->assertSee($older->tracking_code);
});

it('shows an error when no ticket is found', function () {
    $this->get(route('tickets.status', ['q' => 'TK-NOTFOUND']))
        ->assertOk()
        ->assertSee('Nenhum ticket encontrado para o código ou site informado.');
});

it('shows an error when the search is empty', function () {
    $this->get(route('tickets.status').'?q=')
        ->assertOk()
        ->assertSee('Por favor, insira um código de acompanhamento ou o ID do site.');
});

it('is case-insensitive when searching for a tracking code', function () {
    $ticket = Ticket::factory()->create(['tracking_code' => 'TK-ABC123']);

    $this->get(route('tickets.status', ['q' => 'tk-abc123']))
        ->assertOk()
        ->assertSee($ticket->tracking_code);
});
