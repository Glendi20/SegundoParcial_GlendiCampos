<?php

namespace Database\Seeders;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pacientes = Paciente::query()->orderBy('id')->pluck('id')->all();
        $doctores = Doctor::query()->orderBy('id')->pluck('id')->all();

        if (count($pacientes) < 4 || count($doctores) < 3) {
            return;
        }

        $manana = Carbon::tomorrow();

        $citas = [
            [
                'paciente_id' => $pacientes[0],
                'doctor_id' => $doctores[0],
                'inicio' => $manana->copy()->setTime(9, 0),
                'fin' => $manana->copy()->setTime(9, 30),
                'motivo' => 'Consulta general',
                'estado' => EstadoCita::Pendiente,
            ],
            [
                'paciente_id' => $pacientes[1],
                'doctor_id' => $doctores[0],
                'inicio' => $manana->copy()->setTime(10, 0),
                'fin' => $manana->copy()->setTime(10, 30),
                'motivo' => 'Control de rutina',
                'estado' => EstadoCita::Confirmada,
            ],
            [
                'paciente_id' => $pacientes[2],
                'doctor_id' => $doctores[1],
                'inicio' => $manana->copy()->setTime(11, 0),
                'fin' => $manana->copy()->setTime(11, 30),
                'motivo' => 'Chequeo pediatrico',
                'estado' => EstadoCita::Pendiente,
            ],
            [
                'paciente_id' => $pacientes[3],
                'doctor_id' => $doctores[2],
                'inicio' => $manana->copy()->addDays(2)->setTime(15, 0),
                'fin' => $manana->copy()->addDays(2)->setTime(15, 30),
                'motivo' => 'Evaluacion cardiologica',
                'estado' => EstadoCita::Confirmada,
            ],
            [
                'paciente_id' => $pacientes[0],
                'doctor_id' => $doctores[2],
                'inicio' => Carbon::yesterday()->setTime(9, 0),
                'fin' => Carbon::yesterday()->setTime(9, 30),
                'motivo' => 'Seguimiento de tratamiento',
                'estado' => EstadoCita::Atendida,
            ],
            [
                'paciente_id' => $pacientes[1],
                'doctor_id' => $doctores[1],
                'inicio' => Carbon::yesterday()->setTime(16, 0),
                'fin' => Carbon::yesterday()->setTime(16, 30),
                'motivo' => 'Consulta de seguimiento',
                'estado' => EstadoCita::Cancelada,
            ],
        ];

        foreach ($citas as $cita) {
            Cita::query()->updateOrCreate(
                [
                    'paciente_id' => $cita['paciente_id'],
                    'doctor_id' => $cita['doctor_id'],
                    'inicio' => $cita['inicio'],
                ],
                $cita
            );
        }
    }
}
