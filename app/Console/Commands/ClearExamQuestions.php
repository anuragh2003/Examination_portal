<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;

class ClearExamQuestions extends Command
{
    protected $signature = 'exam:clear-questions {exam_id}';
    protected $description = 'Clear all questions from an exam';

    public function handle()
    {
        $examId = $this->argument('exam_id');
        
        $exam = Exam::findOrFail($examId);
        $this->info("Clearing questions from: {$exam->name}");
        
        $count = DB::table('exam_questions')->where('exam_id', $examId)->count();
        DB::table('exam_questions')->where('exam_id', $examId)->delete();
        
        $this->info("✅ Cleared {$count} questions from exam {$examId}");
        
        return 0;
    }
}
