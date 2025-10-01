<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'uuid',
        'total_marks',
        'duration_minutes',
        'status'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($exam) {
            $exam->uuid = Str::uuid(); // generate unique UUID automatically
        });
    }

    /**
     * Get the questions assigned to this exam
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot('order_position')
            ->withTimestamps()
            ->orderBy('order_position');
    }
}
