<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonationIssueController;
use App\Http\Controllers\ImageController;

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

    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
