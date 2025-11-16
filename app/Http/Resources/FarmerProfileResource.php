<?php
// app/Http/Resources/FarmerProfileResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fullName' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone_number,
            'location' => $this->location,
            'profilePhoto' => $this->profile_photo_url ?? '/images/dhanushka.jpg', // Use DB photo or default

            // Data from the 'vendor' relationship
            'farmAddress' => $this->vendor->farm_address,
            'farmName' => $this->vendor->farm_name,
            'farmSize' => $this->vendor->farm_size,
            'experience' => $this->vendor->experience,
            'description' => $this->vendor->farm_description,
        ];
    }
}