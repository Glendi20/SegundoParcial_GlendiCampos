<?php

namespace App\Services;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Collection;

class PacienteService
{
    public function listar(): Collection
    {
        return Paciente::query()->orderBy('nombre')->get();
    }
}
