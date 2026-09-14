<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketStatusHistory>
 */
class TicketStatusHistoryFactory extends Factory
{
    protected $model = TicketStatusHistory::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'from_status' => 'aberto',
            'to_status' => 'em_andamento',
            'changed_by' => null,
        ];
    }
}
