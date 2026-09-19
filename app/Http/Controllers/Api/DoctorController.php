<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorService $doctores) {}

    public function index(): JsonResponse
    {
        return DoctorResource::collection($this->doctores->listar())->response();
    }
}
