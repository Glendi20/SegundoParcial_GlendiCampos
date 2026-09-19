<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctores = [
            ['nombre' => 'Eduardo', 'apellido' => 'Castillo', 'especialidad' => 'Medicina General', 'telefono' => '98001122', 'email' => 'eduardo.castillo@clinica.com'],
            ['nombre' => 'Patricia', 'apellido' => 'Vasquez', 'especialidad' => 'Pediatria', 'telefono' => '98002233', 'email' => 'patricia.vasquez@clinica.com'],
            ['nombre' => 'Roberto', 'apellido' => 'Mendoza', 'especialidad' => 'Cardiologia', 'telefono' => '98003344', 'email' => 'roberto.mendoza@clinica.com'],
            ['nombre' => 'Silvia', 'apellido' => 'Reyes', 'especialidad' => 'Dermatologia', 'telefono' => '98004455', 'email' => 'silvia.reyes@clinica.com'],
        ];

        foreach ($doctores as $doctor) {
            Doctor::query()->updateOrCreate(['email' => $doctor['email']], $doctor);
        }
    }
}
