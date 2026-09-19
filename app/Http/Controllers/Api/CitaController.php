<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoCita;
use App\Http\Controllers\Controller;
use App\Http\Requests\Citas\CambiarEstadoCitaRequest;
use App\Http\Requests\Citas\IndexCitaRequest;
use App\Http\Requests\Citas\StoreCitaRequest;
use App\Http\Requests\Citas\UpdateCitaRequest;
use App\Http\Resources\CitaResource;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;

class CitaController extends Controller
{
    public function __construct(private readonly CitaService $citas) {}

    public function index(IndexCitaRequest $request): JsonResponse
    {
        $citas = $this->citas->listar($request->validated());

        return CitaResource::collection($citas)->response();
    }

    public function store(StoreCitaRequest $request): JsonResponse
    {
        $cita = $this->citas->crear($request->validated());

        return (new CitaResource($cita))->response()->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $cita = $this->citas->obtener($id);

        return (new CitaResource($cita))->response();
    }

    public function update(UpdateCitaRequest $request, int $id): JsonResponse
    {
        $cita = $this->citas->reprogramar($id, $request->validated());

        return (new CitaResource($cita))->response();
    }

    public function cambiarEstado(CambiarEstadoCitaRequest $request, int $id): JsonResponse
    {
        $cita = $this->citas->cambiarEstado($id, $request->enum('estado', EstadoCita::class));

        return (new CitaResource($cita))->response();
    }
}
