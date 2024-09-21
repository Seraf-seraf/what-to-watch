<?php

namespace App\Listeners;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class FilmListener
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $event::dispatch();
    }
}
