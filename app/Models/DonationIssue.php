<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationIssue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'address',
        'latitude',
        'longitude',
        'required_items',
        'status',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that created this donation issue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all donations for this issue.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get all messages for this issue.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(IssueMessage::class);
    }

    /**
     * Get all images for this issue.
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class)->where('imageable_type', self::class);
    }

    /**
     * Get the total amount pledged across all donations.
     */
    public function getTotalPledgedAttribute(): string
    {
        return $this->donations()
            ->where('status', '!=', 'cancelled')
            ->pluck('amount')
            ->filter()
            ->implode(', ');
    }

    /**
     * Get the count of donors.
     */
    public function getDonorCountAttribute(): int
    {
        return $this->donations()
            ->where('status', '!=', 'cancelled')
            ->distinct('user_id')
            ->count('user_id');
    }
}
