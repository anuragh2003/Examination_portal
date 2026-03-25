<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'exam_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->uuid = Str::uuid();
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}