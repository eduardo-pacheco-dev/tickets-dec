<?php

use App\Models\ReportType;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the admin report types page', function () {
    $this->get(route('admin.report-types.index'))->assertRedirect(route('login'));
});

it('renders the admin report types list for admin users', function () {
    $user = User::factory()->admin()->create();
    ReportType::factory()->count(3)->create();

    $this->actingAs($user);

    $this->get(route('admin.report-types.index'))->assertOk();
});

it('denies non-admin users from the report types page', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user);

    $this->get(route('admin.report-types.index'))->assertForbidden();
});

it('denies client users from the report types page', function () {
    $user = User::factory()->client()->create();

    $this->actingAs($user);

    $this->get(route('admin.report-types.index'))->assertForbidden();
});

it('creates a report type', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('openCreate')
        ->set('name', 'Vistoria Elétrica')
        ->set('description', 'Inspeção de instalações elétricas.')
        ->set('sort_order', 1)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('report_types', [
        'name' => 'Vistoria Elétrica',
        'description' => 'Inspeção de instalações elétricas.',
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

it('validates the report type name is required', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('openCreate')
        ->call('save')
        ->assertHasErrors(['name']);

    $this->assertDatabaseCount('report_types', 0);
});

it('edits a report type', function () {
    $user = User::factory()->admin()->create();
    $reportType = ReportType::factory()->create(['name' => 'Original']);

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('openEdit', $reportType->id)
        ->set('name', 'Atualizado')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('report_types', [
        'id' => $reportType->id,
        'name' => 'Atualizado',
    ]);
});

it('toggles a report type active state', function () {
    $user = User::factory()->admin()->create();
    $reportType = ReportType::factory()->create(['is_active' => true]);

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('toggleActive', $reportType->id);

    $this->assertDatabaseHas('report_types', [
        'id' => $reportType->id,
        'is_active' => false,
    ]);
});

it('deletes a report type without tickets', function () {
    $user = User::factory()->admin()->create();
    $reportType = ReportType::factory()->create();

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('delete', $reportType->id);

    $this->assertDatabaseMissing('report_types', ['id' => $reportType->id]);
});

it('does not delete a report type that has tickets', function () {
    $user = User::factory()->admin()->create();
    $reportType = ReportType::factory()->create();
    $ticket = Ticket::factory()->create();
    $ticket->reportTypes()->attach($reportType);

    $this->actingAs($user);

    Livewire::test('admin/report-type-list')
        ->call('delete', $reportType->id);

    $this->assertDatabaseHas('report_types', ['id' => $reportType->id]);
});

it('renders report type names as selectable checkboxes on the ticket form', function () {
    $first = ReportType::factory()->create(['name' => 'Vistoria Elétrica', 'is_active' => true]);
    $second = ReportType::factory()->create(['name' => 'Inspeção Predial', 'is_active' => true]);

    Livewire::test('ticket-form')
        ->assertSee($first->name)
        ->assertSee($second->name);
});

it('creates a ticket with a report type selected', function () {
    $reportType = ReportType::factory()->create(['name' => 'Vistoria Elétrica', 'is_active' => true]);

    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_types', [$reportType->id])
        ->set('report_description', 'Necessário avaliar quadro de energia.')
        ->call('submit')
        ->assertHasNoErrors();

    $ticket = Ticket::first();
    expect($ticket->reportTypes->pluck('id'))->toContain($reportType->id)
        ->and($ticket->report_description)->toBe('Necessário avaliar quadro de energia.');
});

it('creates a ticket with multiple report types selected', function () {
    $first = ReportType::factory()->create(['name' => 'Vistoria Elétrica', 'is_active' => true]);
    $second = ReportType::factory()->create(['name' => 'Inspeção Predial', 'is_active' => true]);
    $third = ReportType::factory()->create(['name' => 'Redes e Telecom', 'is_active' => true]);

    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_types', [$first->id, $second->id, $third->id])
        ->set('report_description', 'Avaliação completa do site.')
        ->call('submit')
        ->assertHasNoErrors();

    $ticket = Ticket::first();
    expect($ticket->reportTypes->pluck('id')->sort()->values()->all())->toBe([$first->id, $second->id, $third->id]);
});

it('rejects an invalid report type when creating a ticket', function () {
    ReportType::factory()->create(['name' => 'Vistoria Elétrica', 'is_active' => true]);

    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_types', [999])
        ->set('report_description', 'Descrição.')
        ->call('submit')
        ->assertHasErrors(['report_types.0']);

    $this->assertDatabaseCount('tickets', 0);
});

it('keeps the free text report field as fallback when no report types exist', function () {
    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-001')
        ->set('technician_name', 'João Silva')
        ->set('report_description', 'Descrição livre.')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'report_description' => 'Descrição livre.',
    ]);
});
