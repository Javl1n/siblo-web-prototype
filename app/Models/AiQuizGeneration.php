<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiQuizGeneration extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'quiz_id',
        'topic',
        'difficulty_level',
        'number_of_questions',
        'generation_prompt',
        'ai_response',
        'status',
        'error_message',
        'requested_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'number_of_questions' => 'integer',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the teacher who requested this generation.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the quiz that was generated.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
