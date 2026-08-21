<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharacterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'status' => $this->status,
            'species' => $this->species,
            'type' => $this->type,
            'gender' => $this->gender,
            'image' => $this->image,
            'origin' => new LocationResource($this->whenLoaded('originLocation')),
            'location' => new LocationResource($this->whenLoaded('currentLocation')),
            'episodes' => EpisodeResource::collection($this->whenLoaded('episodes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
