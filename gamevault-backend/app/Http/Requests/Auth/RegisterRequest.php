<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

//Se encarga de validar los datos del formulario de registro de nuevos usuarios
class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:30', 'unique:users,username', 'alpha_dash'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'         => 'La contraseña debe tener al menos 8 caracteres.',
            'password.letters'     => 'La contraseña debe contener al menos una letra.',
            'password.mixed_case'  => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers'     => 'La contraseña debe contener al menos un número.',
            'password.symbols'     => 'La contraseña debe contener al menos un símbolo (!@#$%...).',
            'password.confirmed'   => 'Las contraseñas no coinciden.',
        ];
    }
}
