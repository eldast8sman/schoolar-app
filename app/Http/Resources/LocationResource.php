<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'location_type' => $this->location_type,
            'syllabus' => $this->syllabus,
            'address' => $this->address,
            'town' => $this->town,
            'lga' => $this->lga,
            'state' => $this->state,
            'country' => $this->country,
            'current_session' => new SchoolSessionResource($this->current_session()),
            'current_term' => new SchoolTermResource($this->current_term()),
        ];
    }
}
