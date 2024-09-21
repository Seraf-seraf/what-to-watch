<?php

namespace App\Jobs;

use App\Models\Film;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteImages implements ShouldQueue
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
     */
    public function handle(): void
    {
        $dbPaths = Film::query()->select(['posterImage', 'previewImage', 'backgroundImage'])
            ->get()
            ->flatMap(function ($film) {
                return [
                    parse_url($film->posterImage, PHP_URL_PATH),
                    parse_url($film->previewImage, PHP_URL_PATH),
                    parse_url($film->backgroundImage, PHP_URL_PATH),
                ];
            })
            ->toArray();


        $dbPaths = str_replace(['/storage/'], '', $dbPaths);

        $directories = ['posterImage', 'previewImage', 'backgroundImage'];
        $allFiles = [];

        foreach ($directories as $directory) {
            $allFiles = array_merge($allFiles, Storage::disk('public')->files($directory));
        }

        $filesToDelete = array_diff($allFiles, $dbPaths);
        foreach ($filesToDelete as $file) {
            Storage::disk('public')->delete($file);
        }
    }
}
