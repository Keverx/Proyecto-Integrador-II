<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
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
            'email' => 'required|email|exists:usuarios,email',
            'codigo_verificacion' => 'required|string|size:6',
        ];
    }
    
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.exists' => 'No encontramos ninguna cuenta con este correo.',
            'codigo_verificacion.required' => 'El código de verificación es obligatorio.',
            'codigo_verificacion.size' => 'El código de verificación debe tener exactamente 6 dígitos.',
        ];
    }
}
