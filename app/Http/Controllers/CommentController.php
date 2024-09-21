<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Film;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function add(Film $film, CommentRequest $request)
    {
        $values = $request->validated();

        $values['user_id'] = Auth::id();
        $comment = $film->comments()->create($values)->load('user');

        return CommentResource::make($comment)->response()->setStatusCode(201);
    }

    public function show(Film $film)
    {
        $comments = $film->comments()
            ->with(['user', 'comments'])
            ->whereNull('comment_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $comments = CommentResource::buildTree($comments);

        return response()->json($comments, 200);
    }

    public function update(Comment $comment, CommentRequest $request)
    {
        $comment->update($request->validated());

        $comment->load('user');

        return CommentResource::make($comment);
    }

    public function delete(Comment $comment)
    {
        $comment->delete();

        return response()->json([], 204);
    }
}
