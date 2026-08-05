<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'donation_issue_id',
        'user_id',
        'message',
    ];

    /**
     * Get the donation issue this message belongs to.
     */
    public function donationIssue(): BelongsTo
    {
        return $this->belongsTo(DonationIssue::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
