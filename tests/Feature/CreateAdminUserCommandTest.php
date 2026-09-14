<?php

use App\Enums\UserRole;
use App\Models\User;

it('creates an admin user with provided details', function () {
    $this->artisan('user:create-admin', [
        'name' => 'Admin Teste',
        'email' => 'admin-test@example.com',
        '--password' => 'password',
    ])->assertExitCode(0);

    $user = User::where('email', 'admin-test@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->role)->toBe(UserRole::Admin);
    expect($user->email_verified_at)->not->toBeNull();
});

it('rejects duplicate emails', function () {
    User::factory()->create(['email' => 'duplicado@example.com']);

    $this->artisan('user:create-admin', [
        'name' => 'Admin Teste',
        'email' => 'duplicado@example.com',
        '--password' => 'password',
    ])->assertExitCode(1);

    expect(User::where('email', 'duplicado@example.com')->count())->toBe(1);
});

it('rejects invalid email', function () {
    $this->artisan('user:create-admin', [
        'name' => 'Admin Teste',
        'email' => 'email-invalido',
        '--password' => 'password',
    ])->assertExitCode(1);

    expect(User::where('email', 'email-invalido')->count())->toBe(0);
});
