<?php

namespace Database\Factories;

use App\Enums\StationAttachmentType;
use App\Models\Station;
use App\Models\StationAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StationAttachment>
 */
class StationAttachmentFactory extends Factory
{
    protected $model = StationAttachment::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'type' => $this->faker->randomElement(StationAttachmentType::cases())->value,
            'original_name' => $this->faker->word().'.pdf',
            'path' => 'station-attachments/'.$this->faker->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(50_000, 5_000_000),
            'uploaded_by' => null,
        ];
    }

    public function tssr(): static
    {
        return $this->state(fn () => ['type' => StationAttachmentType::Tssr->value]);
    }

    public function ppi(): static
    {
        return $this->state(fn () => ['type' => StationAttachmentType::Ppi->value]);
    }
}
