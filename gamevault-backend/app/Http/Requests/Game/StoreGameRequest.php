<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:200', 'unique:games,title'],
            'description'     => ['required', 'string', 'min:50'],
            'developer'       => ['required', 'string', 'max:100'],
            'publisher'       => ['required', 'string', 'max:100'],
            'price'           => ['required', 'numeric', 'min:0'],
            'cover_image'     => ['nullable', 'url'],
            'header_image'    => ['nullable', 'url'],
            'genres'          => ['required', 'array', 'min:1'],
            'genres.*'        => ['string'],
            'platforms'       => ['required', 'array', 'min:1'],
            'platforms.*'     => ['string'],
            'release_date'    => ['required', 'date'],
            'metacritic_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
