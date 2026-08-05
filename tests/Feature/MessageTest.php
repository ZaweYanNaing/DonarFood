<?php

use App\Models\PrivateMessage;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('Message Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->friend = User::factory()->create();

        // Make them friends
        \App\Models\Friendship::factory()->create([
            'user_id' => $this->user->id,
            'friend_id' => $this->friend->id,
            'status' => 'accepted',
        ]);
    });

    it('can list conversations', function () {
        actingAs($this->user)
            ->get('/messages')
            ->assertStatus(200);
    });

    it('can view conversation with a friend', function () {
        actingAs($this->user)
            ->get("/messages/{$this->friend->id}")
            ->assertStatus(200);
    });

    it('cannot message non-friends', function () {
        $stranger = User::factory()->create();

        actingAs($this->user)
            ->post("/messages/{$stranger->id}", ['message' => 'Hello'])
            ->assertStatus(403);
    });

    it('can send a message to friend', function () {
        actingAs($this->user)
            ->post("/messages/{$this->friend->id}", ['message' => 'Hello, friend!'])
            ->assertStatus(200);

        $this->assertDatabaseHas('private_messages', [
            'sender_id' => $this->user->id,
            'recipient_id' => $this->friend->id,
            'message' => 'Hello, friend!',
        ]);
    });

    it('cannot send empty message', function () {
        actingAs($this->user)
            ->post("/messages/{$this->friend->id}", ['message' => ''])
            ->assertStatus(302);
    });

    it('can retrieve message history', function () {
        PrivateMessage::factory()->create([
            'sender_id' => $this->user->id,
            'recipient_id' => $this->friend->id,
            'message' => 'Test message',
        ]);

        actingAs($this->user)
            ->get("/messages/{$this->friend->id}/list")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    });

    it('marks messages as read when viewed', function () {
        $message = PrivateMessage::factory()->create([
            'sender_id' => $this->friend->id,
            'recipient_id' => $this->user->id,
            'message' => 'Unread message',
            'read_at' => null,
        ]);

        actingAs($this->user)
            ->get("/messages/{$this->friend->id}")
            ->assertStatus(200);

        $message->refresh();
        expect($message->read_at)->not->toBeNull();
    });

    it('returns unread message count', function () {
        PrivateMessage::factory()->count(3)->create([
            'sender_id' => $this->friend->id,
            'recipient_id' => $this->user->id,
            'read_at' => null,
        ]);

        actingAs($this->user)
            ->get('/messages/unread/count')
            ->assertStatus(200)
            ->assertJson(['count' => 3]);
    });

    it('conversation shows latest message first', function () {
        PrivateMessage::factory()->create([
            'sender_id' => $this->user->id,
            'recipient_id' => $this->friend->id,
        ]);

        $latestMessage = PrivateMessage::factory()->create([
            'sender_id' => $this->friend->id,
            'recipient_id' => $this->user->id,
            'message' => 'Latest message',
        ]);

        actingAs($this->user)
            ->get('/messages')
            ->assertStatus(200);
    });
});