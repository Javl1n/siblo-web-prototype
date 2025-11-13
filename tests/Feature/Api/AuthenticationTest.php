<?php

use App\Models\PlayerProfile;
use App\Models\User;

uses()->group('api', 'auth');

test('user can register via API', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test Student',
        'username' => 'teststudent',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'trainer_name' => 'Trainer Test',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'username', 'name', 'email', 'user_type'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'username' => 'teststudent',
        'user_type' => 'student',
    ]);

    $this->assertDatabaseHas('player_profiles', [
        'trainer_name' => 'Trainer Test',
    ]);
});

test('user can login via API', function () {
    $user = User::factory()->create([
        'email' => 'student@example.com',
        'password' => bcrypt('password123'),
        'user_type' => 'student',
    ]);

    PlayerProfile::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'student@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'username', 'name', 'email', 'user_type'],
            'token',
        ]);
});

test('teacher cannot login via API', function () {
    $teacher = User::factory()->create([
        'email' => 'teacher@example.com',
        'password' => bcrypt('password123'),
        'user_type' => 'teacher',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'teacher@example.com',
        'password' => 'password123',
    ]);

    $response->assertForbidden()
        ->assertJson([
            'message' => 'This account is not authorized to access the game.',
        ]);
});

test('user can logout via API', function () {
    $user = User::factory()->create(['user_type' => 'student']);
    $token = $user->createToken('game-client')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJson([
            'message' => 'Logout successful. See you next time!',
        ]);

    $this->assertCount(0, $user->tokens);
});

test('registration requires valid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test',
        // Missing required fields
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'email', 'password', 'trainer_name']);
});
