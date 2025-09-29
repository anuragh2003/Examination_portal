<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
