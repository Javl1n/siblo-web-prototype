<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizModule extends Model
{
    protected $fillable = [
        'teacher_id',
        'filename',
        'original_filename',
        'file_type',
        'file_path',
        'file_size',
        'extracted_content',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
