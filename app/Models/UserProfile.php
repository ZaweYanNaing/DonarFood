<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'bio',
        'phone',
        'city',
        'country',
        'birth_date',
        'website',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'is_public' => 'boolean',
    ];

    /**
     * Get the user that owns this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full name from the associated user.
     */
    public function getFullNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Get the profile photo URL.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->user?->profile_photo_path
            ? asset('storage/'.$this->user->profile_photo_path)
            : null;
    }
}
