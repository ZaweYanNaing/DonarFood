<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\DonationIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    /**
     * Display donations for a specific issue.
     */
    public function index(Request $request, DonationIssue $issue): JsonResponse
    {
        $donations = $issue->donations()
            ->with(['user', 'images'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => DonationResource::collection($donations),
            'issue_id' => $issue->id,
        ]);
    }

    /**
     * Show the form for creating a donation pledge.
     */
    public function create(Request $request, DonationIssue $issue): Response
    {
        return Inertia::render('donations/pledge', [
            'issue' => $issue,
            'issue_id' => $issue->id,
        ]);
    }

    /**
     * Store a newly created donation.
     */
    public function store(Request $request, DonationIssue $issue): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'string', 'max:255'],
            'items_description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $donation = $request->user()->donations()->create([
            'donation_issue_id' => $issue->id,
            'amount' => $validated['amount'],
            'items_description' => $validated['items_description'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('donations/'.$donation->id, 'public');
                $donation->images()->create([
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'type' => 'donation',
                ]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation pledge created successfully!')]);

        return to_route('issues.show', $issue);
    }

    /**
     * Update the specified donation.
     */
    public function update(Request $request, Donation $donation): RedirectResponse
    {
        // Only the owner can update their donation
        if ($request->user()->id !== $donation->user_id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('You can only update your own donations.')]);

            return back();
        }

        $validated = $request->validate([
            'amount' => ['sometimes', 'required', 'string', 'max:255'],
            'items_description' => ['sometimes', 'nullable', 'string'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:pledged,confirmed,delivered,cancelled'],
        ]);

        $donation->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation updated successfully.')]);

        return to_route('issues.show', $donation->donationIssue);
    }

    /**
     * Remove the specified donation.
     */
    public function destroy(Request $request, Donation $donation): RedirectResponse
    {
        // Only the owner can delete their donation
        if ($request->user()->id !== $donation->user_id) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('You can only delete your own donations.')]);

            return back();
        }

        $issue = $donation->donationIssue;
        $donation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation cancelled successfully.')]);

        return to_route('issues.show', $issue);
    }
}
