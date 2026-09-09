<?php

use App\Enums\TicketStatus;

test('enum has the correct cases and values', function () {
    expect(TicketStatus::cases())->toHaveCount(3);

    expect(TicketStatus::Aberto->value)->toBe('aberto');
    expect(TicketStatus::EmAndamento->value)->toBe('em_andamento');
    expect(TicketStatus::Resolvido->value)->toBe('resolvido');
});

test('enum returns the correct labels', function () {
    expect(TicketStatus::Aberto->label())->toBe('Aberto');
    expect(TicketStatus::EmAndamento->label())->toBe('Em Andamento');
    expect(TicketStatus::Resolvido->label())->toBe('Resolvido');
});
