<?php

use App\Enums\UserRole;

test('enum has the correct cases and values', function () {
    expect(UserRole::cases())->toHaveCount(4);

    expect(UserRole::Admin->value)->toBe('admin');
    expect(UserRole::Operator->value)->toBe('operator');
    expect(UserRole::Supervisor->value)->toBe('supervisor');
    expect(UserRole::Client->value)->toBe('client');
});

test('enum returns the correct labels', function () {
    expect(UserRole::Admin->label())->toBe('Administrador');
    expect(UserRole::Operator->label())->toBe('Operador');
    expect(UserRole::Supervisor->label())->toBe('Supervisor');
    expect(UserRole::Client->label())->toBe('Cliente');
});

test('enum returns the correct colors', function () {
    expect(UserRole::Admin->color())->toBe('red');
    expect(UserRole::Operator->color())->toBe('blue');
    expect(UserRole::Supervisor->color())->toBe('purple');
    expect(UserRole::Client->color())->toBe('gray');
});

test('admin can manage users', function () {
    expect(UserRole::Admin->canManageUsers())->toBeTrue();
    expect(UserRole::Operator->canManageUsers())->toBeFalse();
    expect(UserRole::Supervisor->canManageUsers())->toBeFalse();
    expect(UserRole::Client->canManageUsers())->toBeFalse();
});

test('admin, operator, and supervisor can manage tickets', function () {
    expect(UserRole::Admin->canManageTickets())->toBeTrue();
    expect(UserRole::Operator->canManageTickets())->toBeTrue();
    expect(UserRole::Supervisor->canManageTickets())->toBeTrue();
    expect(UserRole::Client->canManageTickets())->toBeFalse();
});

test('admin, operator, and supervisor can view tickets', function () {
    expect(UserRole::Admin->canViewTickets())->toBeTrue();
    expect(UserRole::Operator->canViewTickets())->toBeTrue();
    expect(UserRole::Supervisor->canViewTickets())->toBeTrue();
    expect(UserRole::Client->canViewTickets())->toBeFalse();
});
