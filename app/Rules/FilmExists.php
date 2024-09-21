<?php

namespace App\Rules;

use App\Models\Favorite;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FilmExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = auth()->id();

        $exist = Favorite::where('user_id', $userId)
            ->where('film_id', $value)
            ->exists();

        if (!$exist && request()->isMethod('DELETE')) {
            $fail($this->messageFailedDelete());
        }

        if ($exist && request()->isMethod('POST')) {
            $fail($this->messageFailedAppend());
        }
    }

    protected function messageFailedDelete()
    {
        return 'Фильм не добавлялся в избранное';
    }

    protected function messageFailedAppend()
    {
        return 'Фильм уже добавлен в избранное';
    }
}
