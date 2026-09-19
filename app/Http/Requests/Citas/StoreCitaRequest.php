<?php

namespace App\Http\Requests\Citas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctores,id'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],
            'motivo' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('inicio')) {
                return;
            }

            $inicio = $this->date('inicio');

            if ($inicio && $inicio->isPast() && ! $inicio->isToday()) {
                $validator->errors()->add('inicio', 'No se puede agendar una cita en una fecha pasada.');
            }
        });
    }
}
