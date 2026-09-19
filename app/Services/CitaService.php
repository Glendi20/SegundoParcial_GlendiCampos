<?php

namespace App\Services;

use App\Enums\EstadoCita;
use App\Exceptions\CitaConflictException;
use App\Models\Cita;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CitaService
{
    /**
     * @param  array{doctor_id?: int, paciente_id?: int, desde?: string, hasta?: string}  $filtros
     */
    public function listar(array $filtros): Collection
    {
        return Cita::query()
            ->with(['paciente', 'doctor'])
            ->when($filtros['doctor_id'] ?? null, fn ($q, $doctorId) => $q->where('doctor_id', $doctorId))
            ->when($filtros['paciente_id'] ?? null, fn ($q, $pacienteId) => $q->where('paciente_id', $pacienteId))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->where('fin', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->where('inicio', '<=', $hasta))
            ->orderBy('inicio')
            ->get();
    }

    public function obtener(int $id): Cita
    {
        return Cita::with(['paciente', 'doctor'])->findOrFail($id);
    }

    /**
     * @param  array{paciente_id: int, doctor_id: int, inicio: string, fin: string, motivo: string}  $datos
     *
     * @throws CitaConflictException
     */
    public function crear(array $datos): Cita
    {
        return DB::transaction(function () use ($datos) {
            $this->asegurarSinConflicto($datos['doctor_id'], $datos['inicio'], $datos['fin']);

            return Cita::create([
                'paciente_id' => $datos['paciente_id'],
                'doctor_id' => $datos['doctor_id'],
                'inicio' => $datos['inicio'],
                'fin' => $datos['fin'],
                'motivo' => $datos['motivo'],
                'estado' => EstadoCita::Pendiente,
            ])->load(['paciente', 'doctor']);
        });
    }

    /**
     * Reprograma una cita (nueva fecha/hora), validando nuevamente disponibilidad del doctor.
     * Usada por el drag & drop del calendario y por edición manual.
     *
     * @param  array{inicio: string, fin: string, doctor_id?: int}  $datos
     *
     * @throws CitaConflictException
     */
    public function reprogramar(int $id, array $datos): Cita
    {
        return DB::transaction(function () use ($id, $datos) {
            $cita = Cita::lockForUpdate()->findOrFail($id);
            $doctorId = $datos['doctor_id'] ?? $cita->doctor_id;

            $this->asegurarSinConflicto($doctorId, $datos['inicio'], $datos['fin'], exceptCitaId: $cita->id);

            $cita->update([
                'doctor_id' => $doctorId,
                'inicio' => $datos['inicio'],
                'fin' => $datos['fin'],
            ]);

            return $cita->load(['paciente', 'doctor']);
        });
    }

    public function cambiarEstado(int $id, EstadoCita $estado): Cita
    {
        $cita = Cita::findOrFail($id);
        $cita->update(['estado' => $estado]);

        return $cita->load(['paciente', 'doctor']);
    }

    /**
     * @throws CitaConflictException
     */
    private function asegurarSinConflicto(int $doctorId, string $inicio, string $fin, ?int $exceptCitaId = null): void
    {
        $existeConflicto = Cita::query()
            ->solapadas($doctorId, $inicio, $fin, $exceptCitaId)
            ->exists();

        if ($existeConflicto) {
            throw new CitaConflictException(
                'El doctor seleccionado ya tiene una cita activa que se solapa con ese horario.'
            );
        }
    }
}
