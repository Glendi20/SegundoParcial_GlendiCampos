<?php

namespace App\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory;

    protected $table = 'doctores';

    protected $fillable = [
        'nombre',
        'apellido',
        'especialidad',
        'telefono',
        'email',
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function nombreCompleto(): string
    {
        return trim("Dr(a). {$this->nombre} {$this->apellido}");
    }
}
