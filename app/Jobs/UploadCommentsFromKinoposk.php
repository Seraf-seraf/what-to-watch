<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Models\Film;
use App\Services\CommentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadCommentsFromKinoposk implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(CommentService $service, Film $film): void
    {
        $imdb_ids = $film->getModerateFilmsIds();

        foreach ($imdb_ids as $id) {
            $comments = $service->requestComments($id);
            $formattedComments = $service->reformatCommentsFromKinopoisk($comments, $id) ?? [];

            foreach ($formattedComments as $commentData) {
                Comment::query()->updateOrCreate(
                    ['id' => $commentData['id']],
                    $commentData
                );
            }
        }
    }
}
