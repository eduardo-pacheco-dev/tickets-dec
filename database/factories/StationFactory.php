<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'element_type' => $this->faker->randomElement(['ENODE B', 'NODE B', 'BTS']),
            'technology' => $this->faker->randomElement(['LTE', 'UMTS', 'GSM']),
            'address_id' => strtoupper($this->faker->unique()->bothify('AC???_####')),
            'classification' => $this->faker->randomElement(['RANSHARING', 'ACESSO']),
            'area_holder' => $this->faker->randomElement(['IHS BRAZIL', 'AMERICAN TOWER', 'HIGH LINE']),
            'infra_contract_type' => $this->faker->randomElement(['Built-to-Suit', 'Compartilhado']),
            'infra_holder' => $this->faker->randomElement(['IHS BRAZIL', 'AMERICAN TOWER']),
            'infra_type' => 'Greenfield',
            'ev_type' => $this->faker->optional()->randomElement(['TORRE METALICA TRIANGULAR', 'MASTRO']),
            'ev_provider' => $this->faker->optional()->randomElement(['BRASILSAT', 'IBRAP']),
            'observation' => $this->faker->optional(0.3)->sentence(),
            'justification' => null,
            'street_type' => $this->faker->randomElement(['RUA', 'AVENIDA']),
            'street' => $this->faker->streetName,
            'number' => $this->faker->randomElement(['S/N', (string) $this->faker->numberBetween(1, 9999)]),
            'complement' => $this->faker->optional()->word(),
            'neighborhood' => $this->faker->randomElement(['CENTRO', 'VILA NOVA', 'SAO JOSE']),
            'city' => $this->faker->city,
            'state' => 'AC',
            'cep' => $this->faker->numerify('########'),
            'regional' => 'TCO',
            'latitude' => $this->faker->regexify('-10\.[0-9]{6}'),
            'longitude' => $this->faker->regexify('-6[0-9]\.[0-9]{6}'),
            'status' => $this->faker->optional(0.9)->randomElement(['Aquisitado', 'Candidato']),
            'tower_type' => null,
            'aev_nominal' => $this->faker->optional()->randomElement(['0', null]),
            'land_area' => $this->faker->optional()->randomElement(['0', null]),
            'structure_height' => $this->faker->optional(0.8)->randomElement(['40', '60']),
            'external_id' => strtoupper($this->faker->unique()->bothify('ACR###TM')),
            'site_id' => strtoupper($this->faker->unique()->bothify('4G-??####')),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
