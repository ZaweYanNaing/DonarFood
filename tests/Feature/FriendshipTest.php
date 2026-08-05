<?php

use App\Models\Friendship;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

describe('Friendship Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    });

    it('can list own friends', function () {
        Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $this->otherUser->id,
            'status' => 'accepted',
        ]);

        actingAs($this->user)
            ->get('/friends')
            ->assertStatus(200);
    });

    it('can send a friend request', function () {
        actingAs($this->user)
            ->post("/friends/request/{$this->otherUser->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $this->user->id,
            'friend_id' => $this->otherUser->id,
            'status' => 'pending',
        ]);
    });

    it('cannot send request to self', function () {
        actingAs($this->user)
            ->post("/friends/request/{$this->user->id}")
            ->assertStatus(400);
    });

    it('cannot send duplicate friend request', function () {
        Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $this->otherUser->id,
            'status' => 'pending',
        ]);

        actingAs($this->user)
            ->post("/friends/request/{$this->otherUser->id}")
            ->assertStatus(400);
    });

    it('can accept a friend request', function () {
        Friendship::factory()->create([
            'user_id' => $this->otherUser->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        actingAs($this->user)
            ->put("/friends/request/{$this->otherUser->id}", ['action' => 'accept'])
            ->assertStatus(200);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $this->otherUser->id,
            'friend_id' => $this->user->id,
            'status' => 'accepted',
        ]);
    });

    it('can decline a friend request', function () {
        Friendship::factory()->create([
            'user_id' => $this->otherUser->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        actingAs($this->user)
            ->put("/friends/request/{$this->otherUser->id}", ['action' => 'decline'])
            ->assertStatus(200);

        $this->assertDatabaseHas('friendships', [
            'user_id' => $this->otherUser->id,
            'friend_id' => $this->user->id,
            'status' => 'declined',
        ]);
    });

    it('can unfriend a user', function () {
        Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $this->otherUser->id,
            'status' => 'accepted',
        ]);

        actingAs($this->user)
            ->delete("/friends/{$this->otherUser->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('friendships', [
            'user_id' => $this->user->id,
            'friend_id' => $this->otherUser->id,
            'status' => 'accepted',
        ]);
    });

    it('can list pending friend requests', function () {
        Friendship::factory()->create([
            'user_id' => User::factory()->create()->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        actingAs($this->user)
            ->get('/friends/requests')
            ->assertStatus(200);
    });

    it('users become friends when one accepts pending request', function () {
        Friendship::factory()->create([
            'user_id' => $this->otherUser->id,
            'friend_id' => $this->user->id,
            'status' => 'pending',
        ]);

        actingAs($this->user)
            ->put("/friends/request/{$this->otherUser->id}", ['action' => 'accept']);

        $friendship = Friendship::where('user_id', $this->otherUser->id)
            ->where('friend_id', $this->user->id)
            ->first();

        expect($friendship->status)->toBe('accepted')
            ->and($friendship->accepted_at)->not->toBeNull();
    });

    it('search finds users by name', function () {
        User::factory()->create(['name' => 'John Doe']);

        actingAs($this->user)
            ->get('/friends/search?q=John')
            ->assertStatus(200)
            ->assertJsonStructure(['results']);
    });
});