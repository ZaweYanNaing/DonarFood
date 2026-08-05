<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $profile_photo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get donation issues created by this user.
     */
    public function donationIssues(): HasMany
    {
        return $this->hasMany(DonationIssue::class);
    }

    /**
     * Get donations made by this user.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get friendships where this user is the requester.
     */
    public function friendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Get friendships where this user is the friend (recipient).
     */
    public function friendOf(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Get all friends (accepted relationships).
     */
    public function friends(): HasMany
    {
        return $this->friendships()->where('status', 'accepted');
    }

    /**
     * Get pending friend requests sent by this user.
     */
    public function pendingFriendRequests(): HasMany
    {
        return $this->friendships()->where('status', 'pending');
    }

    /**
     * Get pending friend requests received by this user.
     */
    public function pendingFriendRequestsReceived(): HasMany
    {
        return $this->friendOf()->where('status', 'pending');
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(PrivateMessage::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(PrivateMessage::class, 'recipient_id');
    }

    /**
     * Get unread messages received by this user.
     */
    public function unreadMessages(): HasMany
    {
        return $this->receivedMessages()->whereNull('read_at');
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the profile photo URL.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : null;
    }

    /**
     * Check if user is a volunteer.
     */
    public function isVolunteer(): bool
    {
        return $this->donationIssues()->exists();
    }

    /**
     * Check if user is a donator.
     */
    public function isDonator(): bool
    {
        return $this->donations()->exists();
    }
}
