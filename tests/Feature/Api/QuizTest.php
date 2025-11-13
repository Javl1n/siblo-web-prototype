<?php

use App\Models\PlayerProfile;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Quiz;
use App\Models\User;

uses()->group('api', 'quiz');

beforeEach(function () {
    $this->student = User::factory()->create(['user_type' => 'student']);
    PlayerProfile::factory()->create(['user_id' => $this->student->id]);
    $this->token = $this->student->createToken('game-client')->plainTextToken;
});

test('student can list published quizzes', function () {
    Quiz::factory()->count(3)->published()->create();
    Quiz::factory()->create(['is_published' => false]); // Unpublished

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/quizzes');

    $response->assertOk()
        ->assertJsonCount(3, 'quizzes');
});

test('student can view quiz with questions', function () {
    $quiz = Quiz::factory()->published()->create();
    $question = Question::factory()->create(['quiz_id' => $quiz->id]);
    QuestionChoice::factory()->count(4)->create(['question_id' => $question->id]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/quizzes/{$quiz->id}");

    $response->assertOk()
        ->assertJsonStructure([
            'id',
            'title',
            'description',
            'subject',
            'questions' => [
                '*' => [
                    'id',
                    'question_text',
                    'question_type',
                    'points',
                    'choices' => [
                        '*' => ['id', 'choice_text', 'order_index'],
                    ],
                ],
            ],
        ])
        ->assertJsonMissing(['is_correct']); // Ensure correct answers are not exposed
});

test('student can start a quiz attempt', function () {
    $quiz = Quiz::factory()->published()->create();
    Question::factory()->count(5)->create(['quiz_id' => $quiz->id]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/quizzes/{$quiz->id}/start");

    $response->assertCreated()
        ->assertJsonStructure([
            'attempt_id',
            'quiz_id',
            'started_at',
            'expires_at',
        ]);

    $this->assertDatabaseHas('quiz_attempts', [
        'quiz_id' => $quiz->id,
        'student_id' => $this->student->id,
        'is_completed' => false,
    ]);
});

test('student can submit quiz answers and receive rewards', function () {
    $quiz = Quiz::factory()->published()->create(['difficulty_level' => 'medium', 'pass_threshold' => 60]);
    $question = Question::factory()->create(['quiz_id' => $quiz->id, 'points' => 10]);

    $correctChoice = QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'is_correct' => true,
    ]);

    QuestionChoice::factory()->count(3)->create([
        'question_id' => $question->id,
        'is_correct' => false,
    ]);

    $attempt = $quiz->attempts()->create([
        'student_id' => $this->student->id,
        'started_at' => now(),
        'max_score' => 10,
        'attempt_number' => 1,
        'is_completed' => false,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/quiz-attempts/{$attempt->id}/submit", [
            'answers' => [
                [
                    'question_id' => $question->id,
                    'selected_choice_ids' => [$correctChoice->id],
                ],
            ],
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'score',
            'max_score',
            'percentage',
            'passed',
            'rewards' => ['experience_points', 'coins', 'items'],
            'answers',
        ]);

    $this->assertDatabaseHas('quiz_attempts', [
        'id' => $attempt->id,
        'is_completed' => true,
        'score' => 10,
    ]);

    $this->assertDatabaseHas('quiz_rewards', [
        'quiz_attempt_id' => $attempt->id,
        'student_id' => $this->student->id,
    ]);
});

test('student cannot exceed max attempts', function () {
    $quiz = Quiz::factory()->published()->create(['max_attempts' => 2]);
    Question::factory()->create(['quiz_id' => $quiz->id]);

    // Create 2 completed attempts
    $quiz->attempts()->create([
        'student_id' => $this->student->id,
        'started_at' => now(),
        'max_score' => 10,
        'attempt_number' => 1,
        'is_completed' => true,
    ]);

    $quiz->attempts()->create([
        'student_id' => $this->student->id,
        'started_at' => now(),
        'max_score' => 10,
        'attempt_number' => 2,
        'is_completed' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/quizzes/{$quiz->id}/start");

    $response->assertForbidden()
        ->assertJson([
            'message' => 'You have reached the maximum number of attempts for this quiz.',
        ]);
});
