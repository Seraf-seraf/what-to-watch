<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilmsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'posterImage' => $this->posterImage,
            'previewImage' => $this->previewImage,
            'backgroundImage' => $this->backgroundImage,
            'backgroundColor' => $this->backgroundColor,
            'videoLink' => $this->videoLink,
            'previewVideoLink' => $this->previewVideoLink,
            'description' => $this->description,
            'rating' => $this->rating,
            'scoresCount' => $this->scores_count,
            'director' => $this->director,
            'starring' => $this->starring,
            'runTime' => $this->runTime,
            'genre' => $this->genre,
            'released' => $this->released,
            'status' => $this->status,
            'is_favorite' => $this->whenNotNull($this->is_favorite, false)
        ];
    }
}
