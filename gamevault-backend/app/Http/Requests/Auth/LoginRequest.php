<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

//Se encarga de validar los datos del formulario de inicio de sesion
class LoginRequest extends FormRequest
{
public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
