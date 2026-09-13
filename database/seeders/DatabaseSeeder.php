<?php

namespace Database\Seeders;

use App\Models\ReportType;
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

        ReportType::factory()->createMany([
            ['name' => 'Vistoria Elétrica', 'description' => 'Inspeção de instalações elétricas e segurança.', 'sort_order' => 1],
            ['name' => 'Inspeção Predial', 'description' => 'Avaliação estrutural e de conservação do prédio.', 'sort_order' => 2],
            ['name' => 'Redes e Telecom', 'description' => 'Avaliação de rede, cabeamento e telecomunicações.', 'sort_order' => 3],
            ['name' => 'Conformidade Técnica', 'description' => 'Verificação de conformidade com normas técnicas.', 'sort_order' => 4],
        ]);

        $this->call(StationSeeder::class);
    }
}
