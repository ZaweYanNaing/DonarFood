<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendshipResource extends JsonResource
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
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'friend' => new UserMinimalResource($this->whenLoaded('friend')),
            'is_pending' => $this->isPending(),
            'is_accepted' => $this->isAccepted(),
        ];
    }
}