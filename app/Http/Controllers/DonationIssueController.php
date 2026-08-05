<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonationIssueCardResource;
use App\Http\Resources\DonationIssueResource;
use App\Models\DonationIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonationIssueController extends Controller
{
    /**
     * Display a listing of donation issues (public API).
     */
    public function index(Request $request): JsonResponse
    {
        $issues = DonationIssue::with(['user', 'images'])
            ->latest()
            ->paginate(12);

        return response()->json([
            'data' => DonationIssueCardResource::collection($issues),
            'meta' => [
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
                'per_page' => $issues->perPage(),
                'total' => $issues->total(),
            ],
        ]);
    }

    /**
     * Display the donation issues page (Inertia).
     */
    public function home(Request $request): Response
    {
        $issues = DonationIssue::with(['user', 'images'])
            ->latest()
            ->paginate(12);

        return Inertia::render('issues/index', [
            'issues' => DonationIssueCardResource::collection($issues),
            'pagination' => [
                'current_page' => $issues->currentPage(),
                'last_page' => $issues->lastPage(),
                'total' => $issues->total(),
            ],
        ]);
    }

    /**
     * Show a specific donation issue.
     */
    public function show(Request $request, DonationIssue $issue): Response
    {
        $issue->load(['user', 'donations.user', 'messages.user', 'images']);

        return Inertia::render('issues/show', [
            'issue' => new DonationIssueResource($issue),
            'can' => [
                'update' => $request->user()?->can('update', $issue),
                'delete' => $request->user()?->can('delete', $issue),
                'donate' => $request->user() !== null,
                'message' => $request->user() !== null,
            ],
        ]);
    }

    /**
     * Show the form for creating a new donation issue.
     */
    public function create(): Response
    {
        return Inertia::render('issues/create');
    }

    /**
     * Store a newly created donation issue.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'required_items' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $issue = $request->user()->donationIssues()->create($validated);

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('issues/'.$issue->id, 'public');
                $issue->images()->create([
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'type' => 'issue',
                ]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation issue created successfully.')]);

        return to_route('issues.show', $issue);
    }

    /**
     * Update the specified donation issue.
     */
    public function update(Request $request, DonationIssue $issue): RedirectResponse
    {
        $this->authorize('update', $issue);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'required_items' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:open,in_progress,completed,closed'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $issue->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation issue updated successfully.')]);

        return to_route('issues.show', $issue);
    }

    /**
     * Remove the specified donation issue.
     */
    public function destroy(Request $request, DonationIssue $issue): RedirectResponse
    {
        $this->authorize('delete', $issue);

        $issue->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Donation issue deleted successfully.')]);

        return to_route('home');
    }
}
