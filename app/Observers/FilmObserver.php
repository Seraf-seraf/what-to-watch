<?php

namespace App\Observers;

use App\Jobs\PendingFilms;
use App\Jobs\UpdateFilms;
use App\Models\Film;
use Illuminate\Support\Facades\Log;

class FilmObserver
{
    /**
     * Handle the Film "created" event.
     */
    public function created(Film $film): void
    {
        $message = "Created film with imdb_id: {$film->imdb_id}";
        Log::channel('film')->info("Boot event: $message");

        event(new PendingFilms());
    }

    /**
     * Handle the Film "updated" event.
     */
    public function updated(Film $film): void
    {
        event(new UpdateFilms());
        Log::channel('film')->info("Updated film with imdb_id: {$film->imdb_id}");
    }

    /**
     * Handle the Film "deleted" event.
     */
    public function deleted(Film $film): void
    {
        Log::channel('film')->info("Deleted film with imdb_id: {$film->imdb_id}");
    }
}
