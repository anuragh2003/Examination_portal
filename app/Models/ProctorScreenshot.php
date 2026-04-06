<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProctorScreenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'frame_type', // 'screen' or 'face'
        'frame_number',
        'timestamp',
        'filename',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Group screenshots by exam session
    public function scopeByExamSession($query, $studentId, $examId)
    {
        return $query->where('student_id', $studentId)
                     ->where('exam_id', $examId)
                     ->orderBy('frame_number', 'asc');
    }
}
