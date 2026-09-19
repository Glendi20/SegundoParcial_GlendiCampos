<?php

namespace App\Models;

use Database\Factories\PacienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    /** @use HasFactory<PacienteFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email',
        'fecha_nacimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}
