<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:usuarios,email,' . $this->user()->id_usuario . ',id_usuario',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:6',
        ];
    }
}
