<?php

namespace App\Policies;

use App\Models\User;

class FilmPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
