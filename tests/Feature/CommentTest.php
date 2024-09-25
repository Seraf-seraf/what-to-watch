<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateComment(): void
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->make();

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $comment->text,
            'rating' => 5
            ]
        );

        $response->assertStatus(201)
            ->assertJsonFragment(
                [
                'rating' => 5,
                'text' => $comment->text,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name
                ]
                ]
            );
    }

    /**
     * @covers \App\Policies\CommentPolicy::create
     */
    public function testCantCreateComment()
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->make();

        $film = Film::factory()->create(
            [
            'status' => Film::STATUS_MODERATE
            ]
        );

        $response = $this->post(
            route('comments.add', ['film' => $film]),
            [
            'text' => $comment->text,
            'rating' => 5
            ]
        );

        $response->assertStatus(403)
            ->assertJsonFragment(
                [
                'error' => 'Нет доступа к дейсвтию!'
                ]
            );

        $this->assertDatabaseCount(Comment::class, 0);
    }

    public function testUpdateComment(): void
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->for($this->user)->for($this->film)->create();

        $updatedComment = Comment::factory()->make();

        $response = $this->patch(
            route('comments.update', ['comment' => $comment->id]),
            [
            'text' => $updatedComment->text,
            'rating' => 10
            ]
        );

        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                'rating' => 10,
                'text' => $updatedComment->text
                ]
            );

        $this->assertEquals($this->user->id, $response->json('data.user.id'));
    }

    public function testAdminCanUpdateEveryoneComment()
    {
        $this->user->setAsAdmin();
        $this->actingAs($this->user);
        $secondUser = User::factory()->create();

        $comment = Comment::factory()->create(
            [
            'user_id' => $secondUser->id,
            'film_id' => $this->film->id
            ]
        );

        $secondComment = Comment::factory()->make();

        $response = $this->patch(
            route('comments.update', ['comment' => $comment]),
            [
            'text' => $secondComment->text,
            'rating' => $secondComment->rating
            ]
        );

        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                'id' => $comment->id,
                'text' => $secondComment->text,
                'rating' => $secondComment->rating,
                ]
            );

        $this->assertDatabaseHas(
            Comment::class,
            [
            'id' => $comment->id,
            'text' => $secondComment->text,
            'rating' => $secondComment->rating,
            ]
        );
    }

    public function testUsersCantUpdateOthersComments()
    {
        $this->actingAs($this->user);
        $secondUser = User::factory()->create();

        $comment = Comment::factory()->create(
            [
            'user_id' => $secondUser->id,
            'film_id' => $this->film->id
            ]
        );

        $response = $this->patch(
            route('comments.update', ['comment' => $comment]),
            [
            'text' => $comment->text,
            'rating' => $comment->rating
            ]
        );

        $response->assertStatus(403)
            ->assertJsonFragment(
                [
                'error' => 'Нет доступа к дейсвтию!'
                ]
            );

        $this->assertDatabaseHas(
            Comment::class,
            [
            'id' => $comment->id,
            'text' => $comment->text,
            'rating' => $comment->rating,
            ]
        );
    }

    public function testDeleteComment()
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->create(
            [
            'user_id' => $this->user->id,
            'film_id' => $this->film->id
            ]
        );

        $response = $this->delete(route('comments.delete', ['comment' => $comment]));
        $response->assertStatus(204);
        $this->assertDatabaseMissing(
            Comment::class,
            [
            'id' => $comment->id
            ]
        );
    }

    public function testDeleteCommentWithAnswers()
    {
        $this->actingAs($this->user);

        $comment = Comment::factory()->create(
            [
            'user_id' => $this->user->id,
            'film_id' => $this->film->id
            ]
        );

        Comment::factory()->create(
            [
            'user_id' => $this->user->id,
            'film_id' => $this->film->id,
            'rating' => null,
            'parent_id' => $comment->id
            ]
        );

        $response = $this->delete(route('comments.delete', ['comment' => $comment]));
        $response->assertJsonFragment(
            [
            'error' => 'Нет доступа к дейсвтию!'
            ]
        );
    }

    public function testUpdateCommentNotAuthorizedUser()
    {
        $comment = Comment::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id
            ]
        );

        $updatedComment = Comment::factory()->make();

        $response = $this->patch(
            route('comments.update', ['comment' => $comment->id]),
            [
            'text' => $updatedComment->text,
            'rating' => 10
            ]
        );
        $response->assertStatus(401);
    }

    public function testAppendWrongAnswerToComment()
    {
        $comment = Comment::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            'rating' => 1
            ]
        );

        $this->actingAs($this->user);

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $comment->text,
            'rating' => 1,
            'parent_id' => $comment->id
            ]
        );

        $response->assertStatus(422)
            ->assertJsonFragment(
                [
                'rating' => ['Рейтинг не может быть указан в ответе на комментарий']
                ]
            );

        $this->assertDatabaseCount(Comment::class, 1);
    }

    public function testAppendAnswerToComment()
    {
        $comment = Comment::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            'rating' => 1
            ]
        );

        $answer = Comment::factory()->make();

        $this->actingAs($this->user);

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $answer->text,
            'parent_id' => $comment->id
            ]
        );

        $response->assertStatus(201)
            ->assertJsonFragment(
                [
                'text' => $answer->text,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ]
                ]
            );

        $this->assertDatabaseCount(Comment::class, 2);
    }

    public function testCommentWrongRating()
    {
        $comment = Comment::factory()->make();

        $this->actingAs($this->user);

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $comment->text,
            'rating' => 11
            ]
        );

        $response->assertStatus(422)
            ->assertJsonFragment(
                [
                'rating' => ['Максимальное значение у поля rating 10']
                ]
            );

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $comment->text,
            'rating' => -4
            ]
        );

        $response->assertStatus(422)
            ->assertJsonFragment(
                [
                'rating' => ['Минимальное значение у поля rating 1']
                ]
            );

        $this->assertDatabaseMissing(
            Comment::class,
            [
            'id' => $comment->id
            ]
        );
    }

    public function testAppendCommentNotAuthorizedUser()
    {
        $comment = Comment::factory()->make(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            ]
        );

        $response = $this->post(
            route('comments.add', ['film' => $this->film]),
            [
            'text' => $comment->text,
            'rating' => 5
            ]
        );

        $response->assertStatus(401)
            ->assertJsonFragment(
                [
                'error' => 'Нет активной сессии'
                ]
            );

        $this->assertDatabaseMissing(
            Comment::class,
            [
            'text' => $comment->text,
            'rating' => 5,
            'film_id' => $this->film->id
            ]
        );
    }

    public function testAppendAnswerToCommentWithDifferenceFilmId()
    {
        $comment = Comment::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            'rating' => 1
            ]
        );

        $this->actingAs($this->user);

        $film2 = Film::factory()->create(
            [
            'status' => Film::STATUS_READY
            ]
        );

        $response = $this->post(
            route('comments.add', ['film' => $film2->id]),
            [
            'text' => $comment->text,
            'parent_id' => $comment->id,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                'parent_id' => ['Ответ можно добавить только к существующему отзыву у фильма']
                ]
            );
        $this->assertDatabaseCount(Comment::class, 1);
    }

    public function testGetComments()
    {
        $comment = Comment::factory()->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            'parent_id' => null,
            'rating' => 10
            ]
        );


        $answers = Comment::factory(2)->create(
            [
            'film_id' => $this->film->id,
            'user_id' => $this->user->id,
            'rating' => null,
            'parent_id' => $comment->id
            ]
        );

        $firstAnswer = $answers->first();
        $secondAnswer = $answers->get(1);

        $response = $this->get(route('comments.show', ['film' => $this->film->id]));

        dump($response->json());

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'id' => $comment->id,
                    'text' => $comment->text,
                    'rating' => $comment->rating,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ],
                    'answers' => [
                        [
                            'id' => $firstAnswer->id,
                            'text' => $firstAnswer->text,
                            'created_at' => $firstAnswer->created_at->format('Y-m-d H:i:s'),
                            'user' => [
                                'id' => $this->user->id,
                                'name' => $this->user->name,
                            ],
                            'answers' => [],
                        ],
                        [
                            'id' => $secondAnswer->id,
                            'text' => $secondAnswer->text,
                            'created_at' => $secondAnswer->created_at->format('Y-m-d H:i:s'),
                            'user' => [
                                'id' => $this->user->id,
                                'name' => $this->user->name,
                            ],
                            'answers' => [],
                        ]
                    ]
                ]
            );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->film = Film::factory()->create(
            [
            'status' => Film::STATUS_READY
            ]
        );

        $this->user = User::factory()->create(
            [
            'id' => 1
            ]
        );
    }
}
