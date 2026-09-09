<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketResponseSavedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('creates a database notification for each staff member when a ticket is opened', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $supervisor = User::factory()->supervisor()->create();
    $client = User::factory()->client()->create();

    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-0001')
        ->set('technician_name', 'João Técnico')
        ->set('report_description', 'Equipamento apresenta falha intermitente.')
        ->set('checked_in', true)
        ->call('submit')
        ->assertHasNoErrors();

    foreach ([$admin, $operator, $supervisor] as $user) {
        expect($user->notifications)->toHaveCount(1);
        expect($user->notifications->first()->type)->toBe(NewTicketNotification::class);
        expect($user->notifications->first()->data['message'])->toContain('João Técnico');
    }

    expect($client->notifications)->toHaveCount(0);
});

it('emails each staff member when a ticket is opened', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();

    Livewire::test('ticket-form')
        ->set('site_id', 'SITE-0001')
        ->set('technician_name', 'João Técnico')
        ->set('report_description', 'Equipamento apresenta falha intermitente.')
        ->call('submit')
        ->assertHasNoErrors();

    Notification::assertSentTo($admin, NewTicketNotification::class);
    Notification::assertSentTo($operator, NewTicketNotification::class);
});

it('notifies the rest of the staff when a ticket status is updated', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $supervisor = User::factory()->supervisor()->create();
    $ticket = Ticket::factory()->create(['status' => 'aberto']);

    $this->actingAs($admin);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('new_status', 'em_andamento')
        ->call('updateStatus')
        ->assertHasNoErrors();

    expect($operator->notifications)->toHaveCount(1);
    expect($supervisor->notifications)->toHaveCount(1);
    expect($admin->notifications)->toHaveCount(0);

    $notification = $operator->notifications->first();
    expect($notification->type)->toBe(TicketStatusUpdatedNotification::class);
    expect($notification->data['old_status'])->toBe('Aberto');
    expect($notification->data['new_status'])->toBe('Em Andamento');
    expect($notification->data['actor_name'])->toBe($admin->name);
});

it('emails the rest of the staff when a ticket status is updated', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $ticket = Ticket::factory()->create(['status' => 'aberto']);

    $this->actingAs($admin);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('new_status', 'resolvido')
        ->call('updateStatus')
        ->assertHasNoErrors();

    Notification::assertSentTo($operator, TicketStatusUpdatedNotification::class);
    Notification::assertNotSentTo($admin, TicketStatusUpdatedNotification::class);
});

it('notifies the rest of the staff when a ticket response is saved', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $supervisor = User::factory()->supervisor()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('admin_response', 'Segue a resolução do problema.')
        ->call('saveResponse')
        ->assertHasNoErrors();

    expect($operator->notifications)->toHaveCount(1);
    expect($supervisor->notifications)->toHaveCount(1);
    expect($admin->notifications)->toHaveCount(0);

    $notification = $operator->notifications->first();
    expect($notification->type)->toBe(TicketResponseSavedNotification::class);
    expect($notification->data['actor_name'])->toBe($admin->name);
});

it('emails the rest of the staff when a ticket response is saved', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test('admin/ticket-detail', ['ticket' => $ticket])
        ->set('admin_response', 'Segue a resolução do problema.')
        ->call('saveResponse')
        ->assertHasNoErrors();

    Notification::assertSentTo($operator, TicketResponseSavedNotification::class);
    Notification::assertNotSentTo($admin, TicketResponseSavedNotification::class);
});

it('shows the unread badge when there are unread notifications', function () {
    $admin = User::factory()->admin()->create();
    $admin->notify(new NewTicketNotification(Ticket::factory()->create()));

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->assertSeeHtml('data-test="notification-badge"')
        ->assertSeeHtml('data-test="unread-dot"');
});

it('renders no badge when there are no unread notifications', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->assertDontSeeHtml('data-test="notification-badge"');
});

it('marks all notifications as read from the bell', function () {
    $admin = User::factory()->admin()->create();
    $admin->notify(new NewTicketNotification(Ticket::factory()->create()));
    $admin->notify(new NewTicketNotification(Ticket::factory()->create()));

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->call('markAllAsRead')
        ->assertDontSeeHtml('data-test="notification-badge"');

    expect($admin->unreadNotifications)->toHaveCount(0);
});

it('marks a notification as read from the bell and navigates to the ticket', function () {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();
    $admin->notify(new NewTicketNotification($ticket));
    $notificationId = $admin->notifications()->latest()->value('id');

    $this->actingAs($admin);

    Livewire::test('notification-bell')
        ->call('openNotification', $notificationId)
        ->assertRedirect(route('admin.tickets.show', $ticket));

    expect($admin->unreadNotifications)->toHaveCount(0);
});

it('renders the notification list for staff members', function () {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();
    $admin->notify(new NewTicketNotification($ticket));

    $this->actingAs($admin);

    $this->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertSee($ticket->tracking_code)
        ->assertSee('Não lida');
});

it('renders an empty state for the notification list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $this->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertSee('Nenhuma notificação');
});

it('denies client users from the notification list', function () {
    $client = User::factory()->client()->create();

    $this->actingAs($client);

    $this->get(route('admin.notifications.index'))->assertForbidden();
});

it('marks a notification as read from the list page', function () {
    $admin = User::factory()->admin()->create();
    $ticket = Ticket::factory()->create();
    $admin->notify(new NewTicketNotification($ticket));
    $notificationId = $admin->notifications()->latest()->value('id');

    $this->actingAs($admin);

    Livewire::test('admin/notification-list')
        ->call('openNotification', $notificationId)
        ->assertRedirect(route('admin.tickets.show', $ticket));

    expect($admin->unreadNotifications)->toHaveCount(0);
});

it('marks all notifications as read from the list page', function () {
    $admin = User::factory()->admin()->create();
    $admin->notify(new NewTicketNotification(Ticket::factory()->create()));
    $admin->notify(new NewTicketNotification(Ticket::factory()->create()));

    $this->actingAs($admin);

    Livewire::test('admin/notification-list')
        ->call('markAllAsRead')
        ->assertDontSee('Marcar todas como lidas')
        ->assertSee('Lida')
        ->assertOk();

    expect($admin->unreadNotifications)->toHaveCount(0);
});
