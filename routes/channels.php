<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Private chat channel - only authenticated users can listen
Broadcast::channel('private-chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId
        || $user->friends()->where('friend_id', $userId)->exists();
});

// Issue chat channel - anyone authenticated can listen
Broadcast::channel('issue.{issueId}', function ($user, $issueId) {
    return $user !== null;
});

// Notification channel - only the user can receive their notifications
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
