<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizReward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Get all published quizzes available to students.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quiz::with(['questions'])
            ->published()
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        $quizzes = $query->get()->map(function ($quiz) {
            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'subject' => $quiz->subject,
                'topic' => $quiz->topic,
                'difficulty_level' => $quiz->difficulty_level,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'max_attempts' => $quiz->max_attempts,
                'pass_threshold' => $quiz->pass_threshold,
                'question_count' => $quiz->questions_count,
                'is_featured' => $quiz->is_featured,
            ];
        });

        return response()->json([
            'quizzes' => $quizzes,
        ], 200);
    }

    /**
     * Get quiz details with questions (when student starts quiz).
     */
    public function show(Quiz $quiz): JsonResponse
    {
        // Ensure quiz is published
        if (! $quiz->is_published) {
            return response()->json([
                'message' => 'This quiz is not available.',
            ], 404);
        }

        $quiz->load(['questions.choices']);

        return response()->json([
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'subject' => $quiz->subject,
            'topic' => $quiz->topic,
            'difficulty_level' => $quiz->difficulty_level,
            'time_limit_minutes' => $quiz->time_limit_minutes,
            'pass_threshold' => $quiz->pass_threshold,
            'questions' => $quiz->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'points' => $question->points,
                    'order_index' => $question->order_index,
                    'media_url' => $question->media_url,
                    'choices' => $question->choices->sortBy('order_index')->map(function ($choice) {
                        return [
                            'id' => $choice->id,
                            'choice_text' => $choice->choice_text,
                            'order_index' => $choice->order_index,
                            // NOTE: We don't send is_correct to the client!
                        ];
                    })->values(),
                ];
            }),
        ], 200);
    }

    /**
     * Start a quiz attempt.
     */
    public function start(Request $request, Quiz $quiz): JsonResponse
    {
        // Ensure quiz is published
        if (! $quiz->is_published) {
            return response()->json([
                'message' => 'This quiz is not available.',
            ], 404);
        }

        $student = $request->user();

        // Check if student has exceeded max attempts
        if ($quiz->max_attempts) {
            $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->where('is_completed', true)
                ->count();

            if ($attemptCount >= $quiz->max_attempts) {
                return response()->json([
                    'message' => 'You have reached the maximum number of attempts for this quiz.',
                ], 403);
            }
        }

        // Calculate total possible score
        $maxScore = $quiz->questions()->sum('points');

        // Create quiz attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'max_score' => $maxScore,
            'attempt_number' => QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $student->id)
                ->count() + 1,
            'is_completed' => false,
        ]);

        return response()->json([
            'attempt_id' => $attempt->id,
            'quiz_id' => $quiz->id,
            'started_at' => $attempt->started_at->toIso8601String(),
            'expires_at' => $quiz->time_limit_minutes
                ? $attempt->started_at->addMinutes($quiz->time_limit_minutes)->toIso8601String()
                : null,
        ], 201);
    }

    /**
     * Submit quiz answers and calculate results.
     */
    public function submit(Request $request, QuizAttempt $quizAttempt): JsonResponse
    {
        // Ensure the quiz attempt belongs to the authenticated user
        if ($quizAttempt->student_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Ensure quiz attempt is not already submitted
        if ($quizAttempt->is_completed) {
            return response()->json([
                'message' => 'This quiz has already been submitted.',
            ], 400);
        }

        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.selected_choice_ids' => ['required', 'array'],
        ]);

        try {
            DB::beginTransaction();

            $quiz = $quizAttempt->quiz;
            $totalScore = 0;
            $answersResult = [];

            // Process each answer
            foreach ($request->answers as $answerData) {
                $question = $quiz->questions()->find($answerData['question_id']);

                if (! $question) {
                    continue;
                }

                // Get correct choice IDs
                $correctChoiceIds = $question->choices()
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->toArray();

                // Check if answer is correct
                $selectedIds = $answerData['selected_choice_ids'];
                sort($selectedIds);
                sort($correctChoiceIds);

                $isCorrect = $selectedIds === $correctChoiceIds;
                $pointsEarned = $isCorrect ? $question->points : 0;
                $totalScore += $pointsEarned;

                // Save attempt answer
                QuizAttemptAnswer::create([
                    'quiz_attempt_id' => $quizAttempt->id,
                    'question_id' => $question->id,
                    'selected_choice_ids' => json_encode($selectedIds),
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                // Prepare result for response
                $answersResult[] = [
                    'question_id' => $question->id,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                    'explanation' => $question->explanation,
                    'correct_choice_ids' => $correctChoiceIds,
                ];
            }

            // Calculate percentage
            $percentage = $quizAttempt->max_score > 0
                ? round(($totalScore / $quizAttempt->max_score) * 100, 2)
                : 0;

            $passed = $percentage >= $quiz->pass_threshold;

            // Calculate time taken
            $timeTaken = now()->diffInSeconds($quizAttempt->started_at);

            // Update quiz attempt
            $quizAttempt->update([
                'submitted_at' => now(),
                'score' => $totalScore,
                'percentage' => $percentage,
                'time_taken_seconds' => $timeTaken,
                'is_completed' => true,
            ]);

            // Award rewards if passed
            $rewards = null;
            if ($passed) {
                $rewards = $this->awardRewards($quizAttempt, $quiz, $percentage);
            }

            // Update daily activity
            $this->updateDailyActivity($request->user()->id);

            DB::commit();

            return response()->json([
                'score' => $totalScore,
                'max_score' => $quizAttempt->max_score,
                'percentage' => $percentage,
                'passed' => $passed,
                'time_taken_seconds' => $timeTaken,
                'rewards' => $rewards,
                'answers' => $answersResult,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to submit quiz. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Award rewards based on quiz performance.
     */
    protected function awardRewards(QuizAttempt $quizAttempt, Quiz $quiz, float $percentage): array
    {
        // Calculate rewards based on difficulty and performance
        $baseXP = match ($quiz->difficulty_level) {
            'easy' => 50,
            'medium' => 100,
            'hard' => 200,
            default => 75,
        };

        $baseCoins = match ($quiz->difficulty_level) {
            'easy' => 25,
            'medium' => 50,
            'hard' => 100,
            default => 40,
        };

        // Apply performance multiplier
        $performanceMultiplier = $percentage / 100;
        $xpEarned = (int) round($baseXP * $performanceMultiplier);
        $coinsEarned = (int) round($baseCoins * $performanceMultiplier);

        // Bonus for perfect score
        if ($percentage >= 100) {
            $xpEarned = (int) round($xpEarned * 1.5);
            $coinsEarned = (int) round($coinsEarned * 1.5);
        }

        // Create reward record
        QuizReward::create([
            'quiz_attempt_id' => $quizAttempt->id,
            'student_id' => $quizAttempt->student_id,
            'experience_points' => $xpEarned,
            'coins' => $coinsEarned,
            'reward_data' => json_encode([]),
            'awarded_at' => now(),
        ]);

        // Update player profile
        $player = $quizAttempt->student->playerProfile;
        if ($player) {
            $player->increment('experience_points', $xpEarned);
            $player->increment('coins', $coinsEarned);

            // Check for level up (every 1000 XP = 1 level)
            $newLevel = floor($player->experience_points / 1000) + 1;
            if ($newLevel > $player->level) {
                $player->update(['level' => $newLevel]);
            }
        }

        return [
            'experience_points' => $xpEarned,
            'coins' => $coinsEarned,
            'items' => [],
        ];
    }

    /**
     * Update daily activity stats.
     */
    protected function updateDailyActivity(int $playerId): void
    {
        $today = now()->toDateString();

        DailyActivity::updateOrCreate(
            [
                'player_id' => $playerId,
                'activity_date' => $today,
            ],
            [
                'quizzes_completed' => DB::raw('quizzes_completed + 1'),
            ]
        );
    }
}
