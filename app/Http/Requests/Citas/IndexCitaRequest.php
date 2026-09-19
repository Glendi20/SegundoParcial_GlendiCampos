<?php

namespace App\Http\Requests\Citas;

use Illuminate\Foundation\Http\FormRequest;

class IndexCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'integer', 'exists:doctores,id'],
            'paciente_id' => ['sometimes', 'integer', 'exists:pacientes,id'],
            'desde' => ['sometimes', 'date'],
            'hasta' => ['sometimes', 'date', 'after_or_equal:desde'],
        ];
    }
}
