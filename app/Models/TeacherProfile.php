<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialization',
    ];

    /**
     * Get the user that owns this teacher profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all quizzes created by this teacher.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    /**
     * Get all AI quiz generation requests by this teacher.
     */
    public function aiQuizGenerations(): HasMany
    {
        return $this->hasMany(AiQuizGeneration::class, 'teacher_id');
    }

    /**
     * Get quiz analytics for this teacher.
     */
    public function quizAnalytics(): HasMany
    {
        return $this->hasMany(TeacherQuizAnalytics::class, 'teacher_id');
    }
}
