<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'especialidad' => fake()->randomElement([
                'Medicina General',
                'Pediatria',
                'Cardiologia',
                'Dermatologia',
                'Ginecologia',
                'Traumatologia',
            ]),
            'telefono' => fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
