<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentQuestionOrder extends Model
{
    protected $fillable = [
        'student_id',
        'question_id',
        'order_position',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
