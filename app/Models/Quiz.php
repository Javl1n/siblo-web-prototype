<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject',
        'topic',
        'difficulty_level',
        'total_points',
        'time_limit_minutes',
        'pass_threshold',
        'max_attempts',
        'is_ai_generated',
        'ai_generation_prompt',
        'created_by',
        'is_published',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'integer',
            'time_limit_minutes' => 'integer',
            'pass_threshold' => 'integer',
            'max_attempts' => 'integer',
            'is_ai_generated' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user (teacher) who created this quiz.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions for this quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_number');
    }

    /**
     * Get all attempts for this quiz.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get the AI generation record if this quiz was AI-generated.
     */
    public function aiGeneration(): HasOne
    {
        return $this->hasOne(AiQuizGeneration::class);
    }

    /**
     * Get analytics for this quiz.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(TeacherQuizAnalytics::class);
    }

    /**
     * Scope a query to only include published quizzes.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include active quizzes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by difficulty level.
     */
    public function scopeDifficulty($query, string $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope a query to only include featured quizzes.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeSubject($query, string $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Calculate and update total points for the quiz.
     */
    public function calculateTotalPoints(): void
    {
        $this->total_points = $this->questions()->sum('points');
        $this->save();
    }
}
