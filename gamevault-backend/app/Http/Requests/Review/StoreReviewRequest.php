<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

//Gestiona la validación de la creación de reseñas
class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_id'     => ['required', 'exists:games,id'],
            'score'       => ['required', 'integer', 'min:1', 'max:10'],
            'title'       => ['required', 'string', 'max:150'],
            'body'        => ['required', 'string', 'min:20', 'max:2000'],
            'recommended' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.min' => 'La reseña debe tener al menos 20 caracteres.',
            'score.min' => 'La puntuación mínima es 1.',
            'score.max' => 'La puntuación máxima es 10.',
        ];
    }
}
