<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptAnswer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'question_choice_id',
        'is_correct',
        'points_earned',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'points_earned' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * Get the quiz attempt this answer belongs to.
     */
    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    /**
     * Get the question this answer is for.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the choice selected for this answer.
     */
    public function questionChoice(): BelongsTo
    {
        return $this->belongsTo(QuestionChoice::class);
    }
}

