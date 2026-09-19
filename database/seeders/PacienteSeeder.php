<?php

namespace Database\Seeders;

use App\Models\Paciente;
use Illuminate\Database\Seeder;

class PacienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pacientes = [
            ['nombre' => 'Maria', 'apellido' => 'Lopez', 'telefono' => '99001122', 'email' => 'maria.lopez@example.com', 'fecha_nacimiento' => '1990-04-12'],
            ['nombre' => 'Carlos', 'apellido' => 'Ramirez', 'telefono' => '99002233', 'email' => 'carlos.ramirez@example.com', 'fecha_nacimiento' => '1985-11-02'],
            ['nombre' => 'Ana', 'apellido' => 'Gonzalez', 'telefono' => '99003344', 'email' => 'ana.gonzalez@example.com', 'fecha_nacimiento' => '1998-02-20'],
            ['nombre' => 'Jose', 'apellido' => 'Martinez', 'telefono' => '99004455', 'email' => 'jose.martinez@example.com', 'fecha_nacimiento' => '1975-07-08'],
            ['nombre' => 'Lucia', 'apellido' => 'Fernandez', 'telefono' => '99005566', 'email' => 'lucia.fernandez@example.com', 'fecha_nacimiento' => '2001-09-15'],
        ];

        foreach ($pacientes as $paciente) {
            Paciente::query()->updateOrCreate(['email' => $paciente['email']], $paciente);
        }
    }
}
