<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolTermResource extends JsonResource
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
            'term_name' => $this->term_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'position' => $this->position,
            'status' => $this->status
        ];
    }
}
