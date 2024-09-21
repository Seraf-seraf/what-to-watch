<?php

namespace App\Http\Requests;

use App\Rules\CommentBelongsToFilm;
use App\Rules\DisallowRatingInResponse;
use Illuminate\Contracts\Validation\ValidationRule;

class CommentRequest extends BaseFormRequest
{
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
            'comment_id' => [
                new CommentBelongsToFilm(),
                'nullable',
                'exists:comments,id',
            ],
        ];
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
