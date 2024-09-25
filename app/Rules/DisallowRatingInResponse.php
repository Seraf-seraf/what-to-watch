<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DisallowRatingInResponse implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $commentId = request()->input('parent_id');
        if (!is_null($commentId) && !is_null($value)) {
            $fail($this->message());
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Рейтинг не может быть указан в ответе на комментарий';
    }
}
