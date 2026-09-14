<?php

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the reports page', function () {
    $this->get(route('admin.report-types.index'))->assertRedirect(route('login'));
});

it('renders the ticket statuses tab for admin users', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $this->get(route('admin.report-types.index'))
        ->assertOk()
        ->assertSee('Status de Tickets');
});

it('denies non-admin users from the reports page', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user);

    $this->get(route('admin.report-types.index'))->assertForbidden();
});

it('creates a ticket status', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openCreate')
        ->set('name', 'em_analise')
        ->set('label', 'Em Análise')
        ->set('color', 'blue')
        ->set('sort_order', 1)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_statuses', [
        'name' => 'em_analise',
        'label' => 'Em Análise',
        'color' => 'blue',
        'sort_order' => 1,
        'is_active' => true,
    ]);
});

it('validates ticket status fields are required', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openCreate')
        ->call('save')
        ->assertHasErrors(['name', 'label']);

    $this->assertDatabaseCount('ticket_statuses', 0);
});

it('rejects an invalid ticket status name', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openCreate')
        ->set('name', 'Nome Inválido!')
        ->set('label', 'Nome Inválido')
        ->set('color', 'blue')
        ->call('save')
        ->assertHasErrors(['name']);

    $this->assertDatabaseCount('ticket_statuses', 0);
});

it('rejects a duplicate ticket status name', function () {
    $user = User::factory()->admin()->create();
    TicketStatus::factory()->create(['name' => 'em_analise']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openCreate')
        ->set('name', 'em_analise')
        ->set('label', 'Em Análise')
        ->set('color', 'blue')
        ->call('save')
        ->assertHasErrors(['name']);

    $this->assertDatabaseCount('ticket_statuses', 1);
});

it('edits a ticket status', function () {
    $user = User::factory()->admin()->create();
    $status = TicketStatus::factory()->create(['label' => 'Aberto']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openEdit', $status->id)
        ->set('label', 'Em Atendimento')
        ->set('color', 'purple')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ticket_statuses', [
        'id' => $status->id,
        'label' => 'Em Atendimento',
        'color' => 'purple',
    ]);
});

it('does not allow changing the name of a status in use', function () {
    $user = User::factory()->admin()->create();
    $status = TicketStatus::factory()->create(['name' => 'aberto']);
    Ticket::factory()->create(['status' => 'aberto']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('openEdit', $status->id)
        ->set('name', 'em_analise')
        ->call('save')
        ->assertHasErrors(['name']);

    $this->assertDatabaseHas('ticket_statuses', ['id' => $status->id, 'name' => 'aberto']);
});

it('toggles a ticket status active state', function () {
    $user = User::factory()->admin()->create();
    $status = TicketStatus::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('toggleActive', $status->id);

    $this->assertDatabaseHas('ticket_statuses', [
        'id' => $status->id,
        'is_active' => false,
    ]);
});

it('deletes a ticket status not in use', function () {
    $user = User::factory()->admin()->create();
    $status = TicketStatus::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('delete', $status->id);

    $this->assertDatabaseMissing('ticket_statuses', ['id' => $status->id]);
});

it('does not delete a ticket status in use', function () {
    $user = User::factory()->admin()->create();
    $status = TicketStatus::factory()->create(['name' => 'aberto']);
    Ticket::factory()->create(['status' => 'aberto']);

    $this->actingAs($user);

    Livewire::test('admin/ticket-status-list')
        ->call('delete', $status->id);

    $this->assertDatabaseHas('ticket_statuses', ['id' => $status->id]);
});

it('resolves the ticket status label and color from the database', function () {
    $status = TicketStatus::factory()->create(['name' => 'aberto', 'label' => 'Em Análise', 'color' => 'purple']);
    $ticket = Ticket::factory()->create(['status' => 'aberto']);

    expect($ticket->statusLabel())->toBe('Em Análise');
    expect($ticket->statusColor())->toBe('purple');
    expect($ticket->statusModel->is($status))->toBeTrue();
});

it('falls back to the default status label when not in the database', function () {
    $ticket = Ticket::factory()->create(['status' => 'resolvido']);

    expect($ticket->statusLabel())->toBe('Resolvido');
    expect($ticket->statusColor())->toBe('green');
});
