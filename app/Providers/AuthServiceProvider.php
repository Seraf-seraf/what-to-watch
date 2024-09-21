<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\FilmPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
        Film::class => FilmPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('user.isAdmin', function (User $user) {
            return $user->isAdmin();
        });
    }
}
