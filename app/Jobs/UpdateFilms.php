<?php

namespace App\Jobs;

use App\Jobs\Classes\BaseWorkWithFilm;
use App\Models\Film;
use App\Services\FilmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateFilms extends BaseWorkWithFilm implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(FilmService $service, Film $film): void
    {
        $ids = $film->getModerateFilmsIds();

        foreach ($ids as $id) {
            $this->processFilm($id, $service);
        }
    }
}
