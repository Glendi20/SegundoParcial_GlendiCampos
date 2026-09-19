<?php

namespace App\Http\Requests\Citas;

use App\Enums\EstadoCita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CambiarEstadoCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', new Enum(EstadoCita::class)],
        ];
    }
}
