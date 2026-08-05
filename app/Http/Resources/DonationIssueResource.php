<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationIssueResource extends JsonResource
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
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'required_items' => $this->required_items,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'user' => new UserMinimalResource($this->whenLoaded('user')),
            'donations' => DonationResource::collection($this->whenLoaded('donations')),
            'messages' => IssueMessageResource::collection($this->whenLoaded('messages')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'donor_count' => $this->donor_count,
            'total_pledged' => $this->total_pledged,
            'can_update' => $request->user()?->can('update', $this->resource),
            'can_delete' => $request->user()?->can('delete', $this->resource),
        ];
    }
}
