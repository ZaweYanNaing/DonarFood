<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonationIssueController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DonationIssueController::class, 'home'])->name('home');
Route::get('/issues', [DonationIssueController::class, 'index'])->name('issues.index');
Route::resource('issues', DonationIssueController::class)->only(['show', 'create', 'store']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('issues', DonationIssueController::class)->except(['show', 'create', 'store']);
    Route::post('/issues/{issue}/donations', [DonationController::class, 'store'])->name('donations.store');
    Route::resource('donations', DonationController::class)->only(['update', 'destroy']);

    // Image upload routes
    Route::post('/upload/issue/{issue}', [ImageController::class, 'uploadIssue'])->name('upload.issue');
    Route::post('/upload/donation/{donation}', [ImageController::class, 'uploadDonation'])->name('upload.donation');
    Route::post('/upload/profile', [ImageController::class, 'uploadProfile'])->name('upload.profile');
    Route::delete('/upload/{image}', [ImageController::class, 'destroy'])->name('upload.destroy');

    // Friendship routes
    Route::get('/friends', [FriendshipController::class, 'index'])->name('friends.index');
    Route::get('/friends/requests', [FriendshipController::class, 'requests'])->name('friends.requests');
    Route::get('/friends/search', [FriendshipController::class, 'search'])->name('friends.search');
    Route::post('/friends/request/{user}', [FriendshipController::class, 'send'])->name('friends.send');
    Route::put('/friends/request/{user}', [FriendshipController::class, 'respond'])->name('friends.respond');
    Route::delete('/friends/{user}', [FriendshipController::class, 'destroy'])->name('friends.destroy');

    // Profile routes
    Route::get('/profile/me', [ProfileController::class, 'me'])->name('profile.me');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/{user}/donations', [ProfileController::class, 'donations'])->name('profile.donations');
    Route::get('/profile/{user}/issues', [ProfileController::class, 'issues'])->name('profile.issues');

    // Message routes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/{user}', [MessageController::class, 'send'])->name('messages.send');
    Route::get('/messages/{user}/list', [MessageController::class, 'messages'])->name('messages.list');
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount'])->name('messages.unread-count');

    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
