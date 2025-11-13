<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherQuizAnalytics extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'teacher_id',
        'total_attempts',
        'total_completions',
        'average_score',
        'average_time_seconds',
        'highest_score',
        'lowest_score',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'total_attempts' => 'integer',
            'total_completions' => 'integer',
            'average_score' => 'decimal:2',
            'average_time_seconds' => 'integer',
            'highest_score' => 'integer',
            'lowest_score' => 'integer',
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Get the quiz these analytics are for.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the teacher these analytics belong to.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
