<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public static function buildTree($comments)
    {
        return $comments->map(function ($comment) {
            $comment->setRelation('comments', CommentResource::buildTree($comment->comments));
            return new CommentResource($comment);
        });
    }

    public function toArray($request)
    {
        $resource = [
            'id' => $this->id,
            'text' => $this->text,
            'rating' => $this->rating,
            'created_at' => $this->created_at ?? null,
            'user' => $this->whenLoaded('user', function () {
                return ['id' => $this->user->id, 'name' => $this->user->name];
            }),
            'comments' => $this->whenLoaded('comments', function () {
                return CommentResource::collection($this->comments);
            }),
        ];

        return array_filter($resource, function ($value) {
            return !empty($value);
        });
    }

    public static function collection($resource)
    {
        return $resource->map(function ($comment) {
            return new CommentResource($comment);
        });
    }
}
