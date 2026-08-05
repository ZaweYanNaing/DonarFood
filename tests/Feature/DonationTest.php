<?php

use App\Models\Donation;
use App\Models\DonationIssue;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

describe('Donation Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->issue = DonationIssue::factory()->create();
    });

    it('can create a donation pledge for an issue', function () {
        $data = [
            'amount' => '5 kg rice',
            'items_description' => 'Basmati rice, fresh pack',
        ];

        actingAs($this->user)
            ->post("/issues/{$this->issue->id}/donations", $data)
            ->assertRedirect();

        $this->assertDatabaseHas('donations', [
            'user_id' => $this->user->id,
            'donation_issue_id' => $this->issue->id,
            'amount' => $data['amount'],
            'status' => 'pledged',
        ]);
    });

    it('cannot donate without authentication', function () {
        post("/issues/{$this->issue->id}/donations", [
            'amount' => '5 kg rice',
        ])->assertRedirectToLogin();
    });

    it('can update own donation amount', function () {
        $donation = Donation::factory()->create([
            'user_id' => $this->user->id,
            'donation_issue_id' => $this->issue->id,
            'amount' => '5 kg',
        ]);

        put("/donations/{$donation->id}", [
            'amount' => '10 kg rice',
            'items_description' => $donation->items_description,
        ])->assertRedirect();

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'amount' => '10 kg rice',
        ]);
    });

    it('cannot update another user donation', function () {
        $otherUser = User::factory()->create();
        $donation = Donation::factory()->create([
            'user_id' => $otherUser->id,
            'donation_issue_id' => $this->issue->id,
        ]);

        actingAs($this->user)
            ->put("/donations/{$donation->id}", ['amount' => 'Hacked'])
            ->assertForbidden();
    });

    it('can cancel own donation', function () {
        $donation = Donation::factory()->create([
            'user_id' => $this->user->id,
            'donation_issue_id' => $this->issue->id,
        ]);

        actingAs($this->user)
            ->delete("/donations/{$donation->id}")
            ->assertRedirect();

        $this->assertSoftDeleted('donations', ['id' => $donation->id]);
    });

    it('cannot cancel another user donation', function () {
        $otherUser = User::factory()->create();
        $donation = Donation::factory()->create([
            'user_id' => $otherUser->id,
            'donation_issue_id' => $this->issue->id,
        ]);

        actingAs($this->user)
            ->delete("/donations/{$donation->id}")
            ->assertForbidden();
    });

    it('can list donations for an issue', function () {
        Donation::factory()->count(3)->create(['donation_issue_id' => $this->issue->id]);

        $response = get("/api/issues/{$this->issue->id}/donations");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'user',
                    ],
                ],
            ]);
    });

    it('tracks donor count on issue', function () {
        $donors = User::factory()->count(3)->create();
        foreach ($donors as $donor) {
            Donation::factory()->create([
                'user_id' => $donor->id,
                'donation_issue_id' => $this->issue->id,
            ]);
        }

        $this->issue->refresh();
        expect($this->issue->donor_count)->toBe(3);
    });
});