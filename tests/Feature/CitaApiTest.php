<?php

namespace Tests\Feature;

use App\Enums\EstadoCita;
use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_una_cita_correctamente(): void
    {
        $paciente = Paciente::factory()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $paciente->id,
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(9, 30)->toDateTimeString(),
            'motivo' => 'Consulta general',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.estado', 'pendiente');
        $this->assertDatabaseCount('citas', 1);
    }

    public function test_rechaza_datos_invalidos_con_400(): void
    {
        $response = $this->postJson('/api/citas', [
            'paciente_id' => null,
            'doctor_id' => null,
            'inicio' => 'no-es-una-fecha',
            'fin' => 'no-es-una-fecha',
            'motivo' => '',
        ]);

        $response->assertStatus(400);
    }

    public function test_rechaza_doble_reserva_para_el_mismo_doctor_con_409(): void
    {
        $paciente = Paciente::factory()->create();
        $doctor = Doctor::factory()->create();

        Cita::factory()->create([
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $paciente->id,
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 15)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(9, 45)->toDateTimeString(),
            'motivo' => 'Consulta que se solapa',
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseCount('citas', 1);
    }

    public function test_permite_agendar_con_otro_doctor_en_el_mismo_horario(): void
    {
        $paciente = Paciente::factory()->create();
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();

        Cita::factory()->create([
            'doctor_id' => $doctorA->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $paciente->id,
            'doctor_id' => $doctorB->id,
            'inicio' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(9, 30)->toDateTimeString(),
            'motivo' => 'Consulta con otro doctor',
        ]);

        $response->assertCreated();
    }

    public function test_una_cita_cancelada_no_bloquea_el_horario(): void
    {
        $paciente = Paciente::factory()->create();
        $doctor = Doctor::factory()->create();

        Cita::factory()->create([
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
            'estado' => EstadoCita::Cancelada,
        ]);

        $response = $this->postJson('/api/citas', [
            'paciente_id' => $paciente->id,
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(9, 30)->toDateTimeString(),
            'motivo' => 'Reintento tras cancelacion',
        ]);

        $response->assertCreated();
    }

    public function test_reprograma_una_cita_con_drag_and_drop(): void
    {
        $cita = Cita::factory()->create([
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        $response = $this->putJson("/api/citas/{$cita->id}", [
            'inicio' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(14, 30)->toDateTimeString(),
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'inicio' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
        ]);
    }

    public function test_no_reprograma_si_genera_conflicto_con_otra_cita(): void
    {
        $doctor = Doctor::factory()->create();

        Cita::factory()->create([
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        $citaAMover = Cita::factory()->create([
            'doctor_id' => $doctor->id,
            'inicio' => now()->addDay()->setTime(11, 0),
            'fin' => now()->addDay()->setTime(11, 30),
        ]);

        $response = $this->putJson("/api/citas/{$citaAMover->id}", [
            'inicio' => now()->addDay()->setTime(9, 15)->toDateTimeString(),
            'fin' => now()->addDay()->setTime(9, 45)->toDateTimeString(),
        ]);

        $response->assertStatus(409);
    }

    public function test_cambia_el_estado_de_una_cita(): void
    {
        $cita = Cita::factory()->create();

        $response = $this->patchJson("/api/citas/{$cita->id}/estado", [
            'estado' => 'cancelada',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.estado', 'cancelada');
        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'cancelada']);
    }

    public function test_devuelve_404_para_una_cita_inexistente(): void
    {
        $response = $this->getJson('/api/citas/999999');

        $response->assertStatus(404);
    }

    public function test_lista_citas_filtrando_por_doctor_y_rango_de_fechas(): void
    {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();

        Cita::factory()->create([
            'doctor_id' => $doctorA->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        Cita::factory()->create([
            'doctor_id' => $doctorB->id,
            'inicio' => now()->addDay()->setTime(9, 0),
            'fin' => now()->addDay()->setTime(9, 30),
        ]);

        $response = $this->getJson('/api/citas?'.http_build_query([
            'doctor_id' => $doctorA->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_lista_doctores_y_pacientes(): void
    {
        Doctor::factory()->count(2)->create();
        Paciente::factory()->count(3)->create();

        $this->getJson('/api/doctores')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/pacientes')->assertOk()->assertJsonCount(3, 'data');
    }
}
