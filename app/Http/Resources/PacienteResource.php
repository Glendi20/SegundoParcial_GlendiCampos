<?php

namespace App\Http\Resources;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Paciente */
class PacienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'nombre_completo' => $this->nombreCompleto(),
            'telefono' => $this->telefono,
            'email' => $this->email,
            'fecha_nacimiento' => $this->fecha_nacimiento?->toDateString(),
        ];
    }
}
