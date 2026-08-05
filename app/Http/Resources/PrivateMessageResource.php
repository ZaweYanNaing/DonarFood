<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivateMessageResource extends JsonResource
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
            'message' => $this->message,
            'created_at' => $this->created_at->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'sender' => new UserMinimalResource($this->whenLoaded('sender')),
            'recipient' => new UserMinimalResource($this->whenLoaded('recipient')),
            'is_own' => $request->user()?->id === $this->sender_id,
            'is_read' => $this->isRead(),
        ];
    }
}
