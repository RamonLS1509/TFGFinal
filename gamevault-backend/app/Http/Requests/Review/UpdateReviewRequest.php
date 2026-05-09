<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

//Gestiona la validación de la edición de reseñas
class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');
        return $this->user()->id === $review->user_id;
    }

    public function rules(): array
    {
        return [
            'score'       => ['sometimes', 'integer', 'min:1', 'max:10'],
            'title'       => ['sometimes', 'string', 'max:150'],
            'body'        => ['sometimes', 'string', 'min:20', 'max:2000'],
            'recommended' => ['sometimes', 'boolean'],
        ];
    }
}
