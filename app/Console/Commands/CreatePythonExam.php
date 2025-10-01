<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;

class CreatePythonExam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:create-python';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Python Programming Test exam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Create Python Programming Test exam
            $exam = Exam::create([
                'name' => 'Python Programming Test',
                'total_marks' => 100,
                'duration_minutes' => 90,
                'status' => 'active'
            ]);

            $this->info('✅ Exam created successfully!');
            $this->line('📝 Name: ' . $exam->name);
            $this->line('🎯 Total Marks: ' . $exam->total_marks);
            $this->line('⏱️ Duration: ' . $exam->duration_minutes . ' minutes');
            $this->line('🔗 UUID: ' . $exam->uuid);
            $this->line('🌐 Student Access URL: http://127.0.0.1:8001/exam/' . $exam->uuid);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
