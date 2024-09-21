<?php

namespace App\Http\Requests;

use App\Rules\FilmExists;
use Illuminate\Contracts\Validation\ValidationRule;

class FavoriteRequest extends BaseFormRequest
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
            'film_id' => [new FilmExists()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'film_id' => $this->route('film'),
        ]);
    }
}
