<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\StationComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StationComment>
 */
class StationCommentFactory extends Factory
{
    protected $model = StationComment::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'user_id' => User::factory(),
            'body' => $this->faker->paragraph(),
        ];
    }
}
