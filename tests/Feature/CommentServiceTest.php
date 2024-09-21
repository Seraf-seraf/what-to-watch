<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Repositories\interfaces\FilmRepositoryInterface;
use App\Services\CommentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @property FilmRepositoryInterface|MockInterface $mockRepository
 */
class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testRequestCommentsByImdbId()
    {
        $imdb_id = 'tt11111111';
        $film = Film::factory()->create(['imdb_id' => $imdb_id]);

        $this->mockRepository->shouldReceive('getCommentsByFilm')
            ->with($imdb_id)
            ->andReturn(
                [
                'data' => [
                    'id' => 1,
                    'type' => 'Позитивный',
                    'review' => 'Fake review',
                    'createdAt' => (new \DateTime())->getTimestamp(),
                ]
                ]
            );

        $service = new CommentService($this->mockRepository);

        $comments = $service->requestComments($imdb_id);
        $reformatComments = $service->reformatCommentsFromKinopoisk($comments, $imdb_id);

        $this->assertEquals(10, $reformatComments[0]['rating']);

        $emptyResult = $service->reformatCommentsFromKinopoisk([], '');
        $this->assertEmpty($emptyResult);
    }

    public function testReformatRatingCommentFromKinopoisk()
    {
        $service = new CommentService($this->mockRepository);

        $result = $service->reformatRatingCommentFromKinopoisk('Позитивный');
        $this->assertEquals(10, $result);

        $result = $service->reformatRatingCommentFromKinopoisk('Нейтральный');
        $this->assertEquals(5, $result);

        $result = $service->reformatRatingCommentFromKinopoisk('Негативный');
        $this->assertEquals(1, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = $this->mock(FilmRepositoryInterface::class);
    }
}
