<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Aberto = 'aberto';
    case EmAndamento = 'em_andamento';
    case Resolvido = 'resolvido';

    public function label(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::EmAndamento => 'Em Andamento',
            self::Resolvido => 'Resolvido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aberto => 'yellow',
            self::EmAndamento => 'blue',
            self::Resolvido => 'green',
        };
    }
}
