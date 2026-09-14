<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the admin ticket list', function () {
    $response = $this->get(route('admin.tickets.index'));
    $response->assertRedirect(route('login'));
});

it('renders the admin ticket list for admin users', function () {
    $user = User::factory()->admin()->create();
    Ticket::factory()->count(3)->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.index'));
    $response->assertOk();
});

it('renders the admin ticket list for operator users', function () {
    $user = User::factory()->operator()->create();
    Ticket::factory()->count(3)->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.index'));
    $response->assertOk();
});

it('renders the admin ticket list for supervisor users', function () {
    $user = User::factory()->supervisor()->create();
    Ticket::factory()->count(3)->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.index'));
    $response->assertOk();
});

it('denies client users from the admin ticket list', function () {
    $user = User::factory()->client()->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.index'));
    $response->assertForbidden();
});

it('does not show public ticket links in the admin sidebar', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.index'));
    $response->assertOk();
    $response->assertDontSee('Abrir Ticket');
    $response->assertDontSee('Acompanhar Ticket');
});

it('filters tickets by status', function () {
    $user = User::factory()->admin()->create();
    $open = Ticket::factory()->create(['status' => 'aberto']);
    $resolved = Ticket::factory()->create(['status' => 'resolvido']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-list')
        ->set('status', 'aberto')
        ->assertSee($open->tracking_code)
        ->assertDontSee($resolved->tracking_code);
});

it('searches tickets by tracking code, site id, or technician name', function () {
    $user = User::factory()->admin()->create();
    $byCode = Ticket::factory()->create(['tracking_code' => 'TK-SEARCH1']);
    $bySite = Ticket::factory()->create(['site_id' => 'SITE-SEARCH2']);
    $byName = Ticket::factory()->create(['technician_name' => 'Técnico Buscado']);
    $other = Ticket::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-list')
        ->set('search', 'TK-SEARCH1')
        ->assertSee($byCode->tracking_code)
        ->assertDontSee($bySite->tracking_code)
        ->assertDontSee($byName->tracking_code)
        ->assertDontSee($other->tracking_code);
});

it('paginates the ticket list', function () {
    $user = User::factory()->admin()->create();
    Ticket::factory()->count(20)->create();

    $this->actingAs($user);

    $component = Livewire::test('admin/ticket-list');
    expect($component->instance()->tickets->count())->toBe(15);
});

it('renders the admin ticket detail page', function () {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('admin.tickets.show', $ticket));
    $response->assertOk();
});

it('redirects guests away from the admin ticket detail page', function () {
    $ticket = Ticket::factory()->create();

    $response = $this->get(route('admin.tickets.show', $ticket));
    $response->assertRedirect(route('login'));
});

it('saves an admin response to a ticket for admin users', function () {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('admin_response', 'Analisamos o relatório e segue resolução.')
        ->call('saveResponse')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'admin_response' => 'Analisamos o relatório e segue resolução.',
    ]);
});

it('saves an admin response to a ticket for operator users', function () {
    $user = User::factory()->operator()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('admin_response', 'Resposta do operador.')
        ->call('saveResponse')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'admin_response' => 'Resposta do operador.',
    ]);
});

it('saves an admin response to a ticket for supervisor users', function () {
    $user = User::factory()->supervisor()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('admin_response', 'Resposta do supervisor.')
        ->call('saveResponse')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'admin_response' => 'Resposta do supervisor.',
    ]);
});

it('requires an admin response to save', function () {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->call('saveResponse')
        ->assertHasErrors(['admin_response']);
});

it('updates the ticket status for admin users', function () {
    $user = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create(['status' => 'aberto']);
    TicketStatus::factory()->create(['name' => 'em_andamento', 'label' => 'Em Andamento']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('new_status', 'em_andamento')
        ->call('updateStatus')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'em_andamento',
    ]);
});

it('updates the ticket status for supervisor users', function () {
    $user = User::factory()->supervisor()->create();
    $ticket = Ticket::factory()->create(['status' => 'aberto']);
    TicketStatus::factory()->create(['name' => 'em_andamento', 'label' => 'Em Andamento']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('new_status', 'em_andamento')
        ->call('updateStatus')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'em_andamento',
    ]);
});
