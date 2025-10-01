<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    protected $fillable = [
        'text',
        'type',
        'marks',
        'difficulty',
        'tags',
        'status',
        'import_hash'
    ];

    protected $casts = [
        'marks' => 'integer'
    ];

    /**
     * Get the options for this question
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * Get the exams that this question belongs to
     */
    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot('marks')
            ->withTimestamps();
    }

    /**
     * Get tags as an array
     */
    public function getTagsArrayAttribute()
    {
        return $this->tags ? explode(',', $this->tags) : [];
    }

    /**
     * Check if question contains specific tag or text
     */
    public function containsTag($tag)
    {
        return stripos($this->tags, $tag) !== false || 
               stripos($this->text, $tag) !== false;
    }
}
