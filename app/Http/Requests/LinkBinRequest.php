<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkBinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin_qr' => 'required|string'
        ];
    }
}
