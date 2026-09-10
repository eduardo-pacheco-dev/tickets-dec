<?php

use App\Models\Ticket;
use Livewire\Livewire;

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

it('displays the ticket status when a valid code is provided', function () {
    $ticket = Ticket::factory()->create();

    Livewire::test('ticket-status')
        ->set('tracking_code_input', $ticket->tracking_code)
        ->call('search')
        ->assertHasNoErrors()
        ->assertSet('tracking_code_input', $ticket->tracking_code)
        ->assertSet('error', '');
});

it('shows an error when the ticket is not found', function () {
    Livewire::test('ticket-status')
        ->set('tracking_code_input', 'TK-NOTFOUND')
        ->call('search')
        ->assertHasNoErrors()
        ->assertSet('error', 'Ticket não encontrado. Verifique o código e tente novamente.');
});

it('shows an error when the code is empty', function () {
    Livewire::test('ticket-status')
        ->call('search')
        ->assertHasNoErrors()
        ->assertSet('error', 'Por favor, insira um código de acompanhamento.');
});

it('is case-insensitive when searching for a tracking code', function () {
    $ticket = Ticket::factory()->create(['tracking_code' => 'TK-ABC123']);

    Livewire::test('ticket-status')
        ->set('tracking_code_input', 'tk-abc123')
        ->call('search')
        ->assertHasNoErrors()
        ->assertSet('error', '');
});
