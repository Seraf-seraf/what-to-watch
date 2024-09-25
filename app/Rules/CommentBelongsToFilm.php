<?php

namespace App\Rules;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CommentBelongsToFilm implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $film_id = request()->route('film')->id;

        if (!Comment::where('id', $value)->where('film_id', $film_id)->exists()) {
            $fail('Ответ можно добавить только к существующему отзыву у фильма');
        }
    }
}
