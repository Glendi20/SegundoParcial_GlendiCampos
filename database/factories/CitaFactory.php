<?php

namespace Database\Factories;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('now', '+2 weeks');
        $fin = (clone $inicio)->modify('+30 minutes');

        return [
            'paciente_id' => Paciente::factory(),
            'doctor_id' => Doctor::factory(),
            'inicio' => $inicio,
            'fin' => $fin,
            'motivo' => fake()->randomElement([
                'Consulta general',
                'Control de rutina',
                'Dolor de cabeza recurrente',
                'Chequeo anual',
                'Seguimiento de tratamiento',
            ]),
            'estado' => EstadoCita::Pendiente,
        ];
    }
}
