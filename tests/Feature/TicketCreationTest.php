<?php

use App\Models\Ticket;
use Livewire\Livewire;

it('renders the public ticket creation form', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
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
    expect($ticket->status->value)->toBe('aberto');
});
