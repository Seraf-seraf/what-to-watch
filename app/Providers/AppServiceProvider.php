<?php

namespace App\Providers;

use App\Repositories\classes\KinopoiskFilmRepository;
use App\Repositories\interfaces\FilmRepositoryInterface;
use App\Services\CommentService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, Client::class);
        $this->app->when(CommentService::class)
            ->needs(FilmRepositoryInterface::class)
            ->give(KinopoiskFilmRepository::class);

        // @codeCoverageIgnoreStart
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
