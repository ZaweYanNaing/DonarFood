<?php

namespace App\Policies;

use App\Models\DonationIssue;
use App\Models\User;

class DonationIssuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Public access
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, DonationIssue $issue): bool
    {
        return true; // Public access
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DonationIssue $issue): bool
    {
        return $user->id === $issue->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DonationIssue $issue): bool
    {
        return $user->id === $issue->user_id;
    }
}
