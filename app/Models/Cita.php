<?php

namespace App\Models;

use App\Enums\EstadoCita;
use Database\Factories\CitaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    /** @use HasFactory<CitaFactory> */
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'doctor_id',
        'inicio',
        'fin',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'estado' => EstadoCita::class,
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Citas del mismo doctor cuyo rango [inicio, fin) se solapa con el rango dado
     * y cuyo estado todavía ocupa la agenda (excluye canceladas).
     */
    public function scopeSolapadas(Builder $query, int $doctorId, string $inicio, string $fin, ?int $exceptCitaId = null): Builder
    {
        return $query
            ->where('doctor_id', $doctorId)
            ->whereIn('estado', array_map(fn (EstadoCita $e) => $e->value, EstadoCita::estadosActivos()))
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->when($exceptCitaId, fn (Builder $q) => $q->where('id', '!=', $exceptCitaId));
    }
}
