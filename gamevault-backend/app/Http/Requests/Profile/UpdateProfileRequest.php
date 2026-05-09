<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

//Gestiona la validación de la edición del perfil de usuario
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:100'],
            'username' => [
                'sometimes', 'string', 'max:30', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($this->user()->id),
            ],
            'bio'      => ['sometimes', 'nullable', 'string', 'max:300'],
            'avatar'   => ['sometimes', 'nullable', 'url', 'max:500'],
        ];
    }
}
