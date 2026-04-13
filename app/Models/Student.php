<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
     use HasFactory;

    protected $fillable = [
        'exam_id',
        'candidate_name',
        'candidate_email',
        'candidate_contact',
        'candidate_city',
        'role',
        'otp',
        'otp_expires_at',
        'registered_at',
        'started_at',
        'active_session',
        'active_session_started_at',
        'active_session_expires_at',
        'session_token',
        'submitted_at',
        'attempt_completed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'active_session_started_at' => 'datetime',
        'active_session_expires_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'active_session' => 'boolean',
        'attempt_completed' => 'boolean',
    ];

    // Relationship: Student belongs to an Exam
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    // Relationship: Student has many question orders
    public function questionOrders()
    {
        return $this->hasMany(StudentQuestionOrder::class)->orderBy('order_position');
    }
}
