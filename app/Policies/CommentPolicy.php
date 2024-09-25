<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CommentPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Film $film): bool
    {
        return Auth::check() && $film->status == 'ready';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return ($comment->user_id === $user->id) || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return ($comment->user_id === $user->id && $comment->children->isEmpty()) || $user->isAdmin();
    }
}
