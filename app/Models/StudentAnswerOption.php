<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswerOption extends Model
{
    protected $fillable = [
        'student_answer_id',
        'question_option_id',
    ];

    public function studentAnswer()
    {
        return $this->belongsTo(\App\Models\student_answer::class, 'student_answer_id');
    }

    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
