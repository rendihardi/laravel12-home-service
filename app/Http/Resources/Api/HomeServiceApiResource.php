<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeServiceApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'thumbnail' => asset('storage/'.$this->thumbnail),
            'about' => $this->about,
            'duration' => $this->duration,
            'price' => $this->price,
            'is_popular' => $this->is_popular,

            'category' => new CategoryApiResource($this->whenLoaded('category')),
            'benefits' => $this->benefits,
            'testimonials' => ServiceTestimonialApiResource::collection($this->whenLoaded('testimonials')),
        ];
    }
}
