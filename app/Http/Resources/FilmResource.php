<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'posterImage' => $this->resource->posterImage,
            'previewImage' => $this->resource->previewImage,
            'backgroundImage' => $this->resource->backgroundImage,
            'backgroundColor' => $this->resource->backgroundColor,
            'videoLink' => $this->resource->videoLink,
            'previewVideoLink' => $this->resource->previewVideoLink,
            'description' => $this->resource->description,
            'rating' => $this->resource->rating,
            'scoresCount' => $this->resource->scores_count,
            'director' => $this->resource->director,
            'starring' => $this->resource->starring,
            'runTime' => $this->resource->runTime,
            'genre' => $this->resource->genre,
            'released' => $this->resource->released,
            'status' => $this->resource->status,
            'is_favorite' => $this->whenNotNull($this->resource->is_favorite, false)
        ];
    }
}
