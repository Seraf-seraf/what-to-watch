<?php

namespace App\Http\Requests;

use App\Models\Film;
use App\Rules\ImageUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class FilmRequest extends BaseFormRequest
{
    private const STATUS_FILM = [Film::STATUS_READY, Film::STATUS_PENDING, Film::STATUS_MODERATE];

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
            'name' => 'nullable|string|max:255',
            'posterImage' => [new ImageUrl(), 'nullable', 'max:255'],
            'previewImage' => [new ImageUrl(), 'nullable', 'max:255'],
            'backgroundImage' => [new ImageUrl(), 'nullable', 'max:255'],
            'backgroundColor' => 'nullable|hex_color',
            'videoLink' => 'nullable|url|max:255',
            'previewVideoLink' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'director' => 'nullable|string|max:255',
            'starring' => 'nullable|string',
            'runTime' => 'nullable|numeric',
            'genre' => 'nullable|string',
            'released' => 'nullable|int',
            'status' => [Rule::in(self::STATUS_FILM)],
            'imdb_id' => [$this->getUniqRule(), 'sometimes', 'string', 'regex:/^tt\d{7,8}$/', 'unique:films,imdb_id']
        ];
    }

    private function getUniqRule()
    {
        $rule = Rule::unique(Film::class);

        if ($this->isMethod('patch')) {
            return $rule->ignore($this->imdb_id, 'imdb_id');
        }

        return $rule;
    }

    public function messages()
    {
        $fileMaxMessage = 'Максимальный размер файла изображения для :attribute :max';

        return [
            'imdb_id.unique' => 'Фильм с переданным imdb_id уже был добавлен',
            'imdb_id.regex' => 'Формат imdb_id ttXXXXXXX',
            'posterImage.max' => $fileMaxMessage,
            'previewImage.max' => $fileMaxMessage,
            'backgroundImage.max' => $fileMaxMessage,
            'status.in' => 'Разрешенные статусы для фильма: ' . implode(', ', self::STATUS_FILM)
        ];
    }
}
