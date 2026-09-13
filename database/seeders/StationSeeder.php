<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $states = ['AC', 'AL', 'AM', 'BA', 'CE', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];

        foreach ($states as $state) {
            Station::factory()->count(3)->create(['state' => $state]);
        }

        Station::factory()->count(5)->create(['state' => 'AC', 'is_active' => false]);
    }
}
