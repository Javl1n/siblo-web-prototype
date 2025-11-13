<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionChoice extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'choice_text',
        'is_correct',
        'order_number',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_number' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the question this choice belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get all answers that selected this choice.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
