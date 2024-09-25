<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->resource->id,
            'text' => $this->resource->text,
            'rating' => $this->whenNotNull($this->resource->rating),
            'created_at' => $this->resource->created_at->format('Y-m-d H:i:s'),
            'user' => $this->whenLoaded('user', function () {
                return ['id' => $this->resource->user->id, 'name' => $this->resource->user->name];
            })
        ];

        if ($request->isMethod('GET')) {
            $data['answers'] = CommentResource::collection($this->resource->children);
        }

        return $data;
    }
}
