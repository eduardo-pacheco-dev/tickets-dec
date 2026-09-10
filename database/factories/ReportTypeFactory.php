<?php

namespace Database\Factories;

use App\Models\ReportType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportType>
 */
class ReportTypeFactory extends Factory
{
    protected $model = ReportType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(2, true),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => $this->faker->boolean(90),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
