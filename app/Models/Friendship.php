<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
        'accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the user who sent the friend request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the friend (recipient of the request).
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Check if the friendship is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the friendship is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
