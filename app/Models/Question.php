<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'points',
        'order_number',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'order_number' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the quiz this question belongs to.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get all answer choices for this question.
     */
    public function choices(): HasMany
    {
        return $this->hasMany(QuestionChoice::class)->orderBy('order_number');
    }

    /**
     * Get all answers submitted for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    /**
     * Get the correct choice(s) for this question.
     */
    public function correctChoices(): HasMany
    {
        return $this->hasMany(QuestionChoice::class)->where('is_correct', true);
    }
}
