<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class WorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rut' => 'required|string|max:12|unique:workers,rut',
            'nombre' => 'required|string|max:100'
        ];
    }

    public function messages(): array
    {
        return [
            'rut.required' => 'El RUT es obligatorio',
            'rut.unique' => 'Este RUT ya está registrado',
            'nombre.required' => 'El nombre es obligatorio'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422)
        );
    }
}