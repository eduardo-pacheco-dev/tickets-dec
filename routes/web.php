<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'ticket-form')->name('home');
Route::redirect('abrir-ticket', '/');

Route::livewire('acompanhar-ticket', 'ticket-status')->name('tickets.status');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin,operator,supervisor')->group(function () {
        Route::livewire('admin/tickets', 'admin/ticket-list')->name('admin.tickets.index');
        Route::livewire('admin/tickets/{ticket}', 'admin/ticket-detail')->name('admin.tickets.show');
    });

    Route::middleware('role:admin')->group(function () {
        Route::livewire('admin/users', 'admin/user-list')->name('admin.users.index');
        Route::livewire('admin/users/create', 'admin/user-create-form')->name('admin.users.create');
        Route::livewire('admin/users/{user}', 'admin/user-detail')->name('admin.users.show');
        Route::livewire('admin/users/{user}/edit', 'admin/user-edit-form')->name('admin.users.edit');
    });
});

require __DIR__.'/settings.php';
