<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Repositories\classes\FilmRepository;
use App\Services\FilmService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilmServiceTest extends TestCase
{
    public function testFilmRequest()
    {
        $filmId = 'tt1234567';
        $link = 'https://placehold.co/600x300.png';

        $this->filmRepository
            ->shouldReceive('getFilm')
            ->with($filmId)
            ->andReturn(
                [
                'film' => [
                    'Title' => 'Inception',
                    'Plot' => 'A thief who enters the dreams of others to steal secrets from their subconscious is given
                               a final chance at redemption which requires him to do the impossible: "inception",
                               the implantation of another person\'s idea into a target\'s subconscious.',
                    'Director' => 'Christopher Nolan',
                    'Actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page, Tom Hardy',
                    'Runtime' => '148 min',
                    'Genre' => 'Action, Adventure, Sci-Fi',
                    'Year' => '1970',
                    'Poster' => $link
                ],
                'Response' => 'True',
                ]
            );

        $result = $this->filmService->requestFilm($filmId);

        $expected = [
            'film' => [
                'Title' => 'Inception',
                'Plot' => 'A thief who enters the dreams of others to steal secrets from their subconscious is given
                               a final chance at redemption which requires him to do the impossible: "inception",
                               the implantation of another person\'s idea into a target\'s subconscious.',
                'Director' => 'Christopher Nolan',
                'Actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page, Tom Hardy',
                'Runtime' => '148 min',
                'Genre' => 'Action, Adventure, Sci-Fi',
                'Year' => '1970',
                'Poster' => $link
            ],
            'Response' => 'True',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testFailureFilmRequest()
    {
        $filmId = '32343';
        $this->filmRepository
            ->shouldReceive('getFilm')
            ->with($filmId)
            ->andReturn(
                [
                'Response' => 'False',
                ]
            );

        $result = $this->filmService->requestFilm($filmId);

        $this->assertEquals([], $result);
    }

    public function testTransformImdbData()
    {
        $link = 'https://placehold.co/600x300.png';

        $imdbData = [
            'Title' => 'Inception',
            'Plot' =>  'A thief who enters the dreams of others to steal secrets from their subconscious is given
                        a final chance at redemption which requires him to do the impossible: "inception",
                        the implantation of another person\'s idea into a target\'s subconscious.',
            'Director' => 'Christopher Nolan',
            'Actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page, Tom Hardy',
            'Runtime' => '148 min',
            'Genre' => 'Action, Adventure, Sci-Fi',
            'Year' => 1970,
            'Poster' => $link
        ];

        $data = $this->filmService->transformImdbData($imdbData);

        $this->assertEquals(
            [
            'film' => [
                'name' => $imdbData['Title'],
                'backgroundColor' => '#000000',
                'description' => $imdbData['Plot'],
                'director' => $imdbData['Director'],
                'starring' => $imdbData['Actors'],
                'runTime' => (int)filter_var($imdbData['Runtime'], FILTER_SANITIZE_NUMBER_INT),
                'genre' => $imdbData['Genre'],
                'released' => $imdbData['Year'],
                'status' => Film::STATUS_MODERATE
            ],
            'links' => [
                'posterImage' => $link,
                'previewImage' => $link,
                'backgroundImage' => $link,
            ]
            ],
            $data
        );
    }

    public function testSaveFileSuccess()
    {
        $url = 'https://example.com/poster.jpg';
        $type = 'posterImage';
        $name = 'film123';

        Http::fake(
            [
            $url => Http::response('image data', 200)
            ]
        );

        Storage::fake('public');

        $path = $this->filmService->saveFile($url, $type, $name);

        Storage::disk('public')->assertExists($type . DIRECTORY_SEPARATOR . $name . '.jpg');

        $this->assertEquals(Storage::disk('public')->url($type . DIRECTORY_SEPARATOR . $name . '.jpg'), $path);
    }

    public function testSaveFileFailure()
    {
        $url = 'https://example.com/nonexistent.jpg';
        $type = 'posterImage';
        $name = 'film123';

        Http::fake(
            [
            $url => Http::response(null, 404)
            ]
        );

        Storage::fake('public');

        $path = $this->filmService->saveFile($url, $type, $name);

        $this->assertEquals('https://placehold.co/600x300', $path);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->filmRepository = $this->mock(FilmRepository::class);
        $this->filmService = new FilmService($this->filmRepository);
    }
}
