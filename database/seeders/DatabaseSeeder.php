<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->operator()->create([
            'name' => 'Operador',
            'email' => 'operator@example.com',
        ]);

        User::factory()->supervisor()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@example.com',
        ]);

        User::factory()->client()->create([
            'name' => 'Cliente',
            'email' => 'client@example.com',
        ]);
    }
}
