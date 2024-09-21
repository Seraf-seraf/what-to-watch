<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Repositories\classes\FilmRepository;
use App\Services\HttpClientService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Mockery\MockInterface;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

/**
 * @property HttpClientService|MockInterface $mockHttpClientService
 */
class FilmRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testGetCommentsByFilm()
    {
        $imdb_id = 'tt00000000';
        Film::factory()->create(
            [
            'imdb_id' => $imdb_id
            ]
        );

        $repository = new FilmRepository($this->mockHttpClientService);

        $response = $repository->getCommentsByFilm($imdb_id);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testGetFilmFromRepository()
    {
        $imdb_id = 'tt00000000';

        $this->mockHttpClientService->shouldReceive('sendRequest')
            ->once()
            ->andReturn(
                [
                'Title' => 'Made in America',
                'Year' => 2013,
                'Director' => 'John Doe',
                'imdbID' => $imdb_id,
                'Response' => true
                ]
            );

        $repository = new FilmRepository($this->mockHttpClientService);

        $response = $repository->getFilm($imdb_id);

        $this->assertTrue($response['Response']);
    }

    public function testGetFilmHandlesException()
    {
        $imdb_id = 'tt00000000';

        $this->mockHttpClientService
            ->shouldReceive('sendRequest')
            ->once()
            ->andThrow($this->mock(ClientException::class));

        $repository = new FilmRepository($this->mockHttpClientService);
        $response = $repository->getFilm($imdb_id);

        $this->assertEmpty($response);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHttpClientService = $this->mock(HttpClientService::class);
        $this->mockHttpClientService
            ->shouldReceive('createRequest')
            ->andReturn($this->mock(RequestInterface::class));
    }
}
