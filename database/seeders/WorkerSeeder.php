<?php

namespace Database\Seeders;

use App\Models\Worker;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $workers = [
            ['rut' => '12345678-9', 'nombre' => 'Juan Pérez González'],
            ['rut' => '98765432-1', 'nombre' => 'María López Fuentes'],
            ['rut' => '11111111-1', 'nombre' => 'Carlos Rodríguez Díaz'],
            ['rut' => '22222222-2', 'nombre' => 'Ana Martínez Sánchez'],
            ['rut' => '33333333-3', 'nombre' => 'Pedro González Ruiz'],
            ['rut' => '44444444-4', 'nombre' => 'Laura Fernández López'],
            ['rut' => '55555555-5', 'nombre' => 'Diego Ramírez Torres'],
            ['rut' => '66666666-6', 'nombre' => 'Sofía Morales Castro'],
            ['rut' => '77777777-7', 'nombre' => 'Javier Ortega Silva'],
            ['rut' => '88888888-8', 'nombre' => 'Valentina Reyes Muñoz'],
        ];

        foreach ($workers as $worker) {
            Worker::create($worker);
        }
    }
}