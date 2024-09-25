<?php

namespace App\Http\Requests;

use App\Models\Film;
use App\Rules\CommentBelongsToFilm;
use App\Rules\DisallowRatingInResponse;
use Illuminate\Contracts\Validation\ValidationRule;

class CommentRequest extends BaseFormRequest
{
    private ?int $film_id = null;

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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'film_id' => [
                'exists:films,id'
            ],
            'rating' => [
                'int',
                'required_if:comment_id,null',
                new DisallowRatingInResponse(),
                'min:1',
                'max:10',
            ],
            'text' => "{$this->requiredOnPostMethod()}|string|min:50",
            'parent_id' => [
                new CommentBelongsToFilm(),
                'nullable',
                'exists:comments,id',
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->isMethod('POST')) {
            $this->merge([
                'film_id' => $this->film?->id,
            ]);
        }
    }

    private function requiredOnPostMethod(): string
    {
        return $this->isMethod('PATCH') ? 'sometimes' : 'required';
    }

    public function messages(): array
    {
        return [
            'film_id.exists' => 'Для создания комментария нужно выбрать фильм',
            'rating.required_if' => 'Рейтинг не может быть указан в ответе на комментарий',
        ];
    }
}
