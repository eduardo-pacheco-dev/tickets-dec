<?php

namespace Database\Factories;

use App\Models\TicketStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketStatus>
 */
class TicketStatusFactory extends Factory
{
    protected $model = TicketStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->regexify('[a-z]{2}_[a-z]{4}'),
            'label' => $this->faker->words(2, true),
            'color' => $this->faker->randomElement(array_keys(TicketStatus::colorOptions())),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
