<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationIssueCardResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'status' => $this->status,
            'required_items' => $this->required_items,
            'created_at' => $this->created_at->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'user' => new UserMinimalResource($this->whenLoaded('user')),
            'donor_count' => $this->donor_count,
            'has_donations' => $this->donations()->exists(),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
