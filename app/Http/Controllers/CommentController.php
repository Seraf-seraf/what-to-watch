<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Film;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Comment
 * Пользователь может оставлять отзывы к фильмам и отвечать на другие комментарии.
 * Администратор может удалять, обновлять комментарии. Удалить можно только те, на которых нет ответов.
 * Рейтинг фильмов рассчитывается исходя из оставленных оценок в отзывах.
 * Ежедневно комментарии к фильмам загружаются с сервиса кинопоиск.
 */
class CommentController extends Controller
{
    #[Authenticated]
    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[BodyParam('rating', 'int',
        'Рейтинг, который выставляется в комментарии,
        указать можно только в отзыве,
        в ответах рейтинг указать нельзя. От 1 до 10.',
        example: 65)
    ]
    #[BodyParam('parent_id', 'int',
        'Указывается, когда добавляется ответ к комментарию, в ответах нельзя указать рейтинг',
        required: false
    )]
    #[ResponseFromApiResource(CommentResource::class, Comment::class, status: 201)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function add(Film $film, CommentRequest $request)
    {
        $values = $request->validated();
        $values['user_id'] = Auth::id();

        $parentComment = Comment::find($values['parent_id'] ?? null);
        if ($parentComment) {
            $comment = $parentComment->children()->create($values);
        } else {
            $comment = $film->comments()->create($values);
        }

        $comment->load('user');

        return CommentResource::make($comment)->response()->setStatusCode(201);
    }


    #[UrlParam('film_id', 'int', 'ID фильма', example: 65)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    #[ResponseFromApiResource(CommentResource::class, Comment::class, collection: true, paginate: 10)]
    public function show(Film $film)
    {
        $comments = $film->comments()
            ->with(['user', 'children.user'])
            ->whereIsRoot()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $comments->each(function (Comment $comment) {
            $comment->loadAllUsers($comment);
        });

        return CommentResource::collection($comments);
    }

    #[Authenticated]
    #[Endpoint('PATCH api/v1/comments/{comment_id}')]
    #[UrlParam('comment_id', 'int', 'ID комментария', example: 1)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    #[ResponseFromApiResource(CommentResource::class, Comment::class)]
    public function update(Comment $comment, CommentRequest $request)
    {
        $comment->update($request->validated());
        $comment->load('user');

        return CommentResource::make($comment);
    }

    #[Authenticated]
    #[Endpoint('DELETE api/v1/comments/{comment_id}',
        'Пользователь может удалить свой комментарий, на который нет ответов.
         Администратор может удалить любой комментарий, у которого нет ответов'
    )]
    #[UrlParam('comment_id', 'int', 'ID комментария', example: 1)]
    #[Response('{"error": "Нет активной сессии"}', 401)]
    #[Response('{"error": "Нет доступа к дейсвтию!"}', 403)]
    #[Response('{"error": "Запрашиваемая страница не существует"}', 404)]
    public function delete(Comment $comment)
    {
        $comment->delete();

        return response()->json([], 204);
    }
}
