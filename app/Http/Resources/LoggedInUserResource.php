<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoggedInUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $data = [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'email_verified' => $this->email_verified,
            'onboarding_status' => $this->onboarding_status,
            'school' => new SchoolResource($this->school),
            'school_location' => new LocationResource($this->school_location),
            'schools' => $this->user_details()
        ];
        if(isset($this->authorization)){
            $data['authorization'] = $this->authorization;
        }

        return $data;
    }
}
