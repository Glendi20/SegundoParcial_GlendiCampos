<?php

namespace App\Http\Resources;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cita */
class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paciente' => new PacienteResource($this->whenLoaded('paciente')),
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'inicio' => $this->inicio?->toIso8601String(),
            'fin' => $this->fin?->toIso8601String(),
            'motivo' => $this->motivo,
            'estado' => $this->estado->value,
            'estado_label' => $this->estado->etiqueta(),
            'color' => $this->estado->color(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
