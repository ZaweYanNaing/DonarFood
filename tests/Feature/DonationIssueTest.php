<?php

use App\Models\DonationIssue;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

describe('Donation Issue Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('can view public donation issues listing', function () {
        DonationIssue::factory()->count(5)->create();

        $response = get('/api/issues');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'location',
                        'status',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    });

    it('can view a single donation issue', function () {
        $issue = DonationIssue::factory()->create();

        $response = get("/issues/{$issue->id}");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('issues/show')
                ->has('issue')
            );
    });

    it('can create a donation issue when authenticated', function () {
        $data = [
            'title' => 'Food Drive at Community Center',
            'description' => 'We need canned goods for the winter drive.',
            'location' => 'Community Center',
            'required_items' => 'Canned vegetables, Rice, Pasta',
        ];

        actingAs($this->user)
            ->post('/issues', $data)
            ->assertRedirect();

        $this->assertDatabaseHas('donation_issues', [
            'user_id' => $this->user->id,
            'title' => $data['title'],
            'status' => 'open',
        ]);
    });

    it('cannot create an issue without authentication', function () {
        post('/issues', [
            'title' => 'Test Issue',
            'description' => 'Test description',
        ])->assertRedirectToLogin();
    });

    it('can update own donation issue', function () {
        $issue = DonationIssue::factory()->create(['user_id' => $this->user->id]);

        put("/issues/{$issue->id}", [
            'title' => 'Updated Title',
            'description' => $issue->description,
        ])->assertRedirect();

        $this->assertDatabaseHas('donation_issues', [
            'id' => $issue->id,
            'title' => 'Updated Title',
        ]);
    });

    it('cannot update another user donation issue', function () {
        $otherUser = User::factory()->create();
        $issue = DonationIssue::factory()->create(['user_id' => $otherUser->id]);

        actingAs($this->user)
            ->put("/issues/{$issue->id}", ['title' => 'Hacked'])
            ->assertForbidden();
    });

    it('can delete own donation issue', function () {
        $issue = DonationIssue::factory()->create(['user_id' => $this->user->id]);

        actingAs($this->user)
            ->delete("/issues/{$issue->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('donation_issues', ['id' => $issue->id]);
    });

    it('cannot delete another user donation issue', function () {
        $otherUser = User::factory()->create();
        $issue = DonationIssue::factory()->create(['user_id' => $otherUser->id]);

        actingAs($this->user)
            ->delete("/issues/{$issue->id}")
            ->assertForbidden();
    });

    it('filters issues by status', function () {
        DonationIssue::factory()->create(['status' => 'open']);
        DonationIssue::factory()->create(['status' => 'completed']);

        $response = get('/api/issues?status=open');

        $response->assertStatus(200);
    });
});