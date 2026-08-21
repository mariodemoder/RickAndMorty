<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'type' => $this->type,
            'dimension' => $this->dimension,
            'residents' => CharacterResource::collection($this->whenLoaded('residents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
