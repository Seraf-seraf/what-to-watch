<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
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
            'film' => [
                'id' => $this->film->id,
                'name' => $this->film->name,
                'posterImage' => $this->film->posterImage,
                'previewImage' => $this->film->previewImage,
                'backgroundImage' => $this->film->backgroundImage,
                'backgroundColor' => $this->film->backgroundColor,
                'videoLink' => $this->film->videoLink,
                'previewVideoLink' => $this->film->previewVideoLink,
                'description' => $this->film->description,
                'rating' => $this->film->rating,
                'scoresCount' => $this->film->scores_count,
                'director' => $this->film->director,
                'starring' => $this->film->starring,
                'runTime' => $this->film->runTime,
                'genre' => $this->film->genre,
                'released' => $this->film->released,
                'status' => $this->film->status,
            ]
        ];
    }
}
