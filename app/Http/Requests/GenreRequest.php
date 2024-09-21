<?php

namespace App\Http\Requests;

class GenreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:32', 'unique:App\Models\Genre'],
        ];
    }
}
