<?php

namespace App\Http\Requests\Citas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],
            'doctor_id' => ['sometimes', 'integer', 'exists:doctores,id'],
        ];
    }
}
