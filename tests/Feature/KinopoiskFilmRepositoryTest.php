<?php

namespace Tests\Feature;

use App\Repositories\classes\KinopoiskFilmRepository;
use App\Services\HttpClientService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

/**
 * @property HttpClientService|MockInterface $mockHttpClientService
 */
class KinopoiskFilmRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testGetFilmId()
    {
        $imdb_id = 'tt00000000';
        $this->mockHttpClientService->shouldReceive('sendRequest')
            ->andReturn(
                [
                'id' => 1
                ]
            );

        $repository = new KinopoiskFilmRepository($this->mockHttpClientService);

        $response = $repository->getFilm($imdb_id);

        $this->assertEquals(1, $response['id']);
    }

    public function testGetFilmHandlesException()
    {
        $imdb_id = 'tt00000000';

        $this->mockHttpClientService->shouldReceive('sendRequest')
            ->once()
            ->andThrow($this->mock(ClientException::class));

        $repository = new KinopoiskFilmRepository($this->mockHttpClientService);
        $response = $repository->getFilm($imdb_id);

        $this->assertEmpty($response);
    }

    public function testGetCommentsByFilmHandlesException()
    {
        $imdb_id = 'tt00000000';

        $this->mockHttpClientService->shouldReceive('sendRequest')
            ->andThrow($this->mock(ClientException::class));

        $repository = new KinopoiskFilmRepository($this->mockHttpClientService);

        $response = $repository->getCommentsByFilm($imdb_id);

        $this->assertEmpty($response);
    }

    public function testGetCommentByFilm()
    {
        $imdb_id = 'tt00000000';
        $film_id = 123;
        $baseHeaders = [
            'X-API-KEY' => 1111,
            'accept' => 'application/json'
        ];

        $baseApiUrl = 'https://api.kinopoisk.dev/v1.4';
        $page = 1;


        $uri = "$baseApiUrl/review?" .
            "page=$page" .
            '&limit=250' .
            '&selectFields=id' .
            '&selectFields=type' .
            '&selectFields=review' .
            '&selectFields=createdAt' .
            '&notNullFields=type' .
            '&notNullFields=review' .
            '&notNullFields=id' .
            "&movieId=$film_id";


        $this->mockHttpClientService->shouldReceive('createRequest')
            ->with('GET', $uri, $baseHeaders)
            ->andReturn($this->mock(RequestInterface::class));

        $this->mockHttpClientService
            ->shouldReceive('sendRequest')
            ->andReturn(
                [
                'docs' => [['id' => 1, 'text' => 'good'], ['id' => 2, 'text' => 'bad']],
                'pages' => $page,
                ]
            );

        $repository = new KinopoiskFilmRepository($this->mockHttpClientService);

        $comments = $repository->getCommentsByFilm($imdb_id);

        $this->assertCount(2, $comments);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHttpClientService = $this->mock(HttpClientService::class);
        $this->mockHttpClientService->shouldReceive('createRequest')
            ->andReturn($this->mock(RequestInterface::class));
    }
}
