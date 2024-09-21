<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $extensions = config('filesystems.img_extensions');

        $pathInfo = pathinfo(parse_url($value, PHP_URL_PATH));

        if (!isset($pathInfo['extension']) || !in_array($pathInfo['extension'], $extensions)) {
            $fail($this->getMessage($extensions));
        }
    }

    protected function getMessage(array $extensions): string
    {
        return 'Ссылка должна вести на картинку: https://.../image.png; Допустимые расширения: ' .
            implode(', ', $extensions);
    }
}
