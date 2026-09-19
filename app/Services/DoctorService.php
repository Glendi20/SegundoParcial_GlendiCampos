<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;

class DoctorService
{
    public function listar(): Collection
    {
        return Doctor::query()->orderBy('nombre')->get();
    }
}
