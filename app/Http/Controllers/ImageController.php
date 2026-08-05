<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationIssue;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Allowed mime types for image uploads.
     */
    private const array ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Maximum file size in bytes (5MB).
     */
    private const int MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Upload images to a donation issue.
     */
    public function uploadIssue(Request $request, DonationIssue $issue): JsonResponse
    {
        $this->authorize('update', $issue);

        $request->validate([
            'images.*' => [
                'required',
                'file',
                'image',
                'mimetypes:'.implode(',', self::ALLOWED_MIMES),
                'max:'.self::MAX_FILE_SIZE,
            ],
        ]);

        $images = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('issues/'.$issue->id, 'public');

            $uploadedImage = $issue->images()->create([
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'type' => 'issue',
            ]);

            $images[] = $uploadedImage;
        }

        return response()->json([
            'message' => __('Images uploaded successfully.'),
            'images' => $images,
        ]);
    }

    /**
     * Upload images to a donation.
     */
    public function uploadDonation(Request $request, Donation $donation): JsonResponse
    {
        // Only the donation owner can upload images
        if ($request->user()->id !== $donation->user_id) {
            return response()->json(['message' => __('Unauthorized.')], 403);
        }

        $request->validate([
            'images.*' => [
                'required',
                'file',
                'image',
                'mimetypes:'.implode(',', self::ALLOWED_MIMES),
                'max:'.self::MAX_FILE_SIZE,
            ],
        ]);

        $images = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('donations/'.$donation->id, 'public');

            $uploadedImage = $donation->images()->create([
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'type' => 'donation',
            ]);

            $images[] = $uploadedImage;
        }

        return response()->json([
            'message' => __('Images uploaded successfully.'),
            'images' => $images,
        ]);
    }

    /**
     * Upload profile photo.
     */
    public function uploadProfile(Request $request): JsonResponse
    {
        $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimetypes:'.implode(',', self::ALLOWED_MIMES),
                'max:'.self::MAX_FILE_SIZE,
            ],
        ]);

        $image = $request->file('image');
        $path = $image->store('profiles/'.$request->user()->id, 'public');

        // Delete old profile photo if exists
        if ($request->user()->profile_photo_path) {
            Storage::disk('public')->delete($request->user()->profile_photo_path);
        }

        $request->user()->update(['profile_photo_path' => $path]);

        return response()->json([
            'message' => __('Profile photo uploaded successfully.'),
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Delete an image.
     */
    public function destroy(Request $request, Image $image): JsonResponse
    {
        // Check if user owns the imageable model
        $canDelete = match ($image->imageable_type) {
            DonationIssue::class => $request->user()->id === $image->imageable->user_id,
            Donation::class => $request->user()->id === $image->imageable->user_id,
            default => false,
        };

        if (! $canDelete) {
            return response()->json(['message' => __('Unauthorized.')], 403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($image->path);

        // Delete database record
        $image->delete();

        return response()->json([
            'message' => __('Image deleted successfully.'),
        ]);
    }
}
