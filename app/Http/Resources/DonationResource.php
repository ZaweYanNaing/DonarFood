<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'amount' => $this->amount,
            'items_description' => $this->items_description,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'user' => new UserMinimalResource($this->whenLoaded('user')),
            'donation_issue_id' => $this->donation_issue_id,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'can_update' => $request->user()?->id === $this->user_id,
            'can_delete' => $request->user()?->id === $this->user_id,
        ];
    }
}
