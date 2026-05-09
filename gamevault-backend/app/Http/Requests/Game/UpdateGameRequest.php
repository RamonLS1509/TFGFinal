<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

//Gestiona la validación de la edición de juegos
class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'string', 'max:200'],
            'description'     => ['sometimes', 'string', 'min:50'],
            'developer'       => ['sometimes', 'string', 'max:100'],
            'publisher'       => ['sometimes', 'string', 'max:100'],
            'price'           => ['sometimes', 'numeric', 'min:0'],
            'cover_image'     => ['nullable', 'url'],
            'genres'          => ['sometimes', 'array', 'min:1'],
            'platforms'       => ['sometimes', 'array', 'min:1'],
            'release_date'    => ['sometimes', 'date'],
            'is_active'       => ['sometimes', 'boolean'],
            'metacritic_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
