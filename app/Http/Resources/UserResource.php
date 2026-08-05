<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->when($request->user()?->id === $this->id, $this->email),
            'profile_photo_url' => $this->profile_photo_url,
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            'created_at' => $this->created_at->toIso8601String(),
            'is_friend' => $this->relationLoaded('friendships') || $request->user()?->friends()->where('friend_id', $this->id)->exists(),
            'friend_status' => $this->when($request->user(), function () use ($request) {
                if ($request->user()->id === $this->id) {
                    return 'self';
                }
                $friendship = $request->user()->friendships()->where('friend_id', $this->id)->first()
                    ?? $request->user()->friendOf()->where('user_id', $this->id)->first();

                return $friendship?->status ?? null;
            }),
        ];
    }
}
