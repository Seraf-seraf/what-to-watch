<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $collection = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->file)) {
            $collection['file'] = $this->file;
        }

        return $collection;
    }
}
