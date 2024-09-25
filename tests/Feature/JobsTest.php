<?php

namespace Tests\Feature;

use App\Jobs\PendingFilms;
use App\Models\Film;
use App\Repositories\classes\FilmRepository;
use App\Services\FilmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @property FilmRepository|MockInterface $mockFilmRepository
 * @property FilmService|MockInterface $mockFilmService
 */
class JobsTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleProcessesPendingFilms()
    {
        $imdb_id = 'tt00000000';

        $film = Film::factory()->create(['imdb_id' => $imdb_id, 'status' => Film::STATUS_PENDING]);

        $this->mockFilmService
            ->shouldReceive('requestFilm')
            ->with($imdb_id)
            ->andReturn(
                [
                'Title' => 'Inception',
                'Plot' => 'A thief who enters dreams...',
                'Director' => 'Christopher Nolan',
                'Actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt',
                'Runtime' => '148 min',
                'Genre' => 'Action, Adventure, Sci-Fi',
                'Year' => 2010,
                'Poster' => 'https://placehold.co/600x300',
                ]
            );

        $this->mockFilmService
            ->shouldReceive('transformImdbData')
            ->andReturn(
                [
                'film' => [
                    'name' => 'Inception',
                    'description' => 'A thief who enters dreams...',
                    'director' => 'Christopher Nolan',
                    'genre' => 'Action, Adventure, Sci-Fi',
                    'runTime' => 148,
                    'released' => 2010
                ],
                'links' => [
                    'posterImage' => 'https://placehold.co/600x300'
                ]
                ]
            );

        $this->mockFilmService
            ->shouldReceive('saveFile')
            ->andReturn('https://placehold.co/600x300');


        $job = new PendingFilms();
        $job->handle($this->mockFilmService, new Film());

        $this->assertDatabaseHas(
            Film::class,
            [
            'name' => 'Inception',
            'description' => 'A thief who enters dreams...',
            'director' => 'Christopher Nolan',
            'runTime' => 148,
            'released' => 2010,
            'posterImage' => 'https://placehold.co/600x300'
            ]
        );

        $film = Film::query()->find($film->id);
        $this->assertEquals(['Action', 'Adventure', 'Sci-Fi'], $film->genre);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockFilmService = $this->mock(FilmService::class);
    }
}
