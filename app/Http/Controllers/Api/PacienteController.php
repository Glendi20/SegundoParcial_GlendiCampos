<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PacienteResource;
use App\Services\PacienteService;
use Illuminate\Http\JsonResponse;

class PacienteController extends Controller
{
    public function __construct(private readonly PacienteService $pacientes) {}

    public function index(): JsonResponse
    {
        return PacienteResource::collection($this->pacientes->listar())->response();
    }
}
