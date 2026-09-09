<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tracking_code' => Ticket::generateTrackingCode(),
            'site_id' => $this->faker->bothify('SITE-####'),
            'technician_name' => $this->faker->name(),
            'report_description' => $this->faker->sentence(),
            'checked_in' => $this->faker->boolean(),
            'status' => $this->faker->randomElement(['aberto', 'em_andamento', 'resolvido']),
        ];
    }
}
