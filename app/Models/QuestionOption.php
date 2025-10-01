<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'text',
        'is_correct'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    /**
     * Get the question that owns this option
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
