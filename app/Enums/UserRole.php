<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case Supervisor = 'supervisor';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Operator => 'Operador',
            self::Supervisor => 'Supervisor',
            self::Client => 'Cliente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'red',
            self::Operator => 'blue',
            self::Supervisor => 'purple',
            self::Client => 'gray',
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    public function canManageTickets(): bool
    {
        return in_array($this, [self::Admin, self::Operator]);
    }

    public function canViewTickets(): bool
    {
        return in_array($this, [self::Admin, self::Operator, self::Supervisor]);
    }
}
