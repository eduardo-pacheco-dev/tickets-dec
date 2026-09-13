<?php

use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\StationTemplateController;
use App\Http\Controllers\TicketTrackingController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'ticket-form')->name('home');
Route::redirect('abrir-ticket', '/');

Route::get('acompanhar-ticket', [TicketTrackingController::class, 'show'])->name('tickets.status');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::post('push-subscription', [PushSubscriptionController::class, 'store'])->name('push-subscription.store');
    Route::delete('push-subscription', [PushSubscriptionController::class, 'destroy'])->name('push-subscription.destroy');

    Route::middleware('role:admin,operator,supervisor')->group(function () {
        Route::livewire('admin/tickets', 'admin/ticket-list')->name('admin.tickets.index');
        Route::livewire('admin/tickets/{ticket}', 'admin/ticket-detail')->name('admin.tickets.show');
        Route::livewire('admin/notifications', 'admin/notification-list')->name('admin.notifications.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::livewire('admin/users', 'admin/user-list')->name('admin.users.index');
        Route::livewire('admin/users/create', 'admin/user-create-form')->name('admin.users.create');
        Route::livewire('admin/users/{user}', 'admin/user-detail')->name('admin.users.show');
        Route::livewire('admin/users/{user}/edit', 'admin/user-edit-form')->name('admin.users.edit');
        Route::livewire('admin/report-types', 'admin/report-type-list')->name('admin.report-types.index');
        Route::livewire('admin/stations', 'admin/station-list')->name('admin.stations.index');
        Route::get('admin/stations/import-template', StationTemplateController::class)->name('admin.stations.import-template');
        Route::livewire('admin/stations/{station}', 'admin/station-detail')->name('admin.stations.show');
    });
});

require __DIR__.'/settings.php';
