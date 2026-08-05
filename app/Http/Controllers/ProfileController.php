<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonationIssueCardResource;
use App\Http\Resources\DonationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's own profile page.
     */
    public function me(Request $request): InertiaResponse
    {
        $user = $request->user()->load(['profile', 'donations.donationIssue', 'donationIssues']);

        return Inertia::render('profile/me', [
            'user' => new UserResource($user),
            'my_donations' => DonationResource::collection(
                $user->donations()->with(['donationIssue'])->latest()->limit(10)->get()
            ),
            'my_issues' => DonationIssueCardResource::collection(
                $user->donationIssues()->latest()->limit(10)->get()
            ),
            'friend_count' => $user->friends()->count(),
            'donation_count' => $user->donations()->count(),
            'issue_count' => $user->donationIssues()->count(),
        ]);
    }

    /**
     * Display a user's public profile.
     */
    public function show(Request $request, User $user): InertiaResponse|JsonResponse
    {
        // Check if profile is public
        if ($user->profile && ! $user->profile->is_public) {
            // Only show if viewing own profile or is a friend
            if (! $request->user() || ($request->user()->id !== $user->id && ! $this->areFriends($request->user(), $user))) {
                return Inertia::render('profile/private', [
                    'user_name' => $user->name,
                ]);
            }
        }

        $user->load(['profile', 'donations.donationIssue.user', 'donationIssues.images']);

        $isOwn = $request->user()?->id === $user->id;
        $isFriend = $this->areFriends($request->user(), $user);

        return Inertia::render('profile/show', [
            'profile_user' => new UserResource($user),
            'is_own' => $isOwn,
            'is_friend' => $isFriend,
            'friend_id' => $isOwn ? null : $user->id,
            'donations' => DonationResource::collection(
                $user->donations()->with(['donationIssue'])->latest()->paginate(10)
            ),
            'issues' => DonationIssueCardResource::collection(
                $user->donationIssues()->with(['images'])->latest()->paginate(10)
            ),
            'stats' => [
                'donations_count' => $user->donations()->count(),
                'issues_count' => $user->donationIssues()->count(),
                'friends_count' => $user->friends()->count(),
            ],
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        // Update user name if provided
        if (isset($validated['name'])) {
            $user->update(['name' => $validated['name']]);
            unset($validated['name']);
        }

        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated successfully.')]);

        return to_route('profile.me');
    }

    /**
     * Get the user's donation activity (API).
     */
    public function donations(Request $request, User $user): JsonResponse
    {
        $donations = $user->donations()
            ->with(['donationIssue', 'images'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => DonationResource::collection($donations),
            'meta' => $this->getPaginationMeta($donations),
        ]);
    }

    /**
     * Get the user's created issues (API).
     */
    public function issues(Request $request, User $user): JsonResponse
    {
        $issues = $user->donationIssues()
            ->with(['user', 'images'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => DonationIssueCardResource::collection($issues),
            'meta' => $this->getPaginationMeta($issues),
        ]);
    }

    /**
     * Check if two users are friends.
     */
    private function areFriends(?User $user, User $other): bool
    {
        if (! $user) {
            return false;
        }

        return $user->friends()->where('friend_id', $other->id)->exists()
            || $other->friends()->where('friend_id', $user->id)->exists();
    }

    /**
     * Get pagination meta data.
     */
    private function getPaginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
