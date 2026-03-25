<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_answer extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'question_id',
        'answer_text',
        'status',
        'awarded_marks',
    ];

    protected $casts = [
        // 'chosen_option_ids' => 'array', // Removed, now using pivot table
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class,'question_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Relationship: StudentAnswer has many selected options
    public function selectedOptions()
    {
        return $this->hasMany(StudentAnswerOption::class);
    }
}

