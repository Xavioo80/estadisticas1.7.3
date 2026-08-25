<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroGlobalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => 'required|date|before_or_equal:today',
            'medico' => 'required|string|max:255',
            'prof' => 'nullable|string|max:100',
            'edad' => 'nullable|integer|min:0|max:120',
            'sexo' => 'nullable|in:M,F',
            'ano' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'mes' => 'required|string',
            'cod_1' => 'nullable|string|max:50',
            'diagnostico_1' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'medico.required' => 'El médico es obligatorio',
            'edad.min' => 'La edad debe ser mayor a 0',
            'edad.max' => 'La edad no puede ser mayor a 120',
            'sexo.in' => 'El sexo debe ser M o F',
        ];
    }
}
