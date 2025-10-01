<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use App\Models\Question;
use App\Services\QuestionSelector;

class AssignQuestionsToExam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:assign-questions {exam_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign questions to an exam using intelligent selection with subject filtering';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $examId = $this->argument('exam_id');
        
        try {
            $exam = Exam::findOrFail($examId);
            
            $this->info('📝 Assigning questions to: ' . $exam->name);
            $this->line('🎯 Target marks: ' . $exam->total_marks);
            
            // Check available questions
            $totalQuestions = Question::where('status', 'active')->count();
            $this->line('📚 Available questions: ' . $totalQuestions);
            
            if ($totalQuestions === 0) {
                $this->error('❌ No active questions found in database');
                $this->line('💡 Please import questions first using: php artisan csv:import');
                return 1;
            }
            
            // Use QuestionSelector to intelligently select questions
            $questionSelector = new QuestionSelector();
            
            // Add filters based on exam name/subject
            $filters = [];
            $examName = strtolower($exam->name);
            
            // Automatically detect subject from exam name
            if (str_contains($examName, 'python')) {
                $filters['tags'] = 'python';
            } elseif (str_contains($examName, 'php')) {
                $filters['tags'] = 'php';
            } elseif (str_contains($examName, 'javascript') || str_contains($examName, 'js')) {
                $filters['tags'] = 'javascript';
            } elseif (str_contains($examName, 'java')) {
                $filters['tags'] = 'java';
            }
            // Add more subjects as needed
            
            $result = $questionSelector->selectQuestions($exam->total_marks, $filters);
            
            if (!$result['success'] || empty($result['questions'])) {
                $this->error('❌ No questions could be selected for this exam');
                $this->line('Reason: ' . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
            
            $selectedQuestions = $result['questions'];
            
            // Clear existing questions from exam first
            $exam->questions()->detach();
            
            // Assign selected questions to the exam
            foreach ($selectedQuestions as $index => $questionData) {
                $exam->questions()->attach($questionData['id'], [
                    'order_position' => $index + 1
                ]);
            }
            
            $this->info('✅ Questions assigned successfully!');
            $this->line('📊 Total questions: ' . count($selectedQuestions));
            $this->line('🎯 Total marks: ' . $result['total_marks']);
            $this->line('🧠 Algorithm used: ' . $result['algorithm_used']);
            $this->line('📝 Exam: ' . $exam->name);
            $this->line('🔗 UUID: ' . $exam->uuid);
            
            // Display selection metadata
            if (isset($result['selection_metadata'])) {
                $metadata = $result['selection_metadata'];
                $this->line('');
                $this->line('📈 Selection Details:');
                $this->line('   • Avg marks per question: ' . $metadata['avg_marks_per_question']);
                
                if (isset($metadata['difficulty_distribution'])) {
                    $diff = $metadata['difficulty_distribution'];
                    $this->line('   • Easy questions: ' . ($diff['easy'] ?? 0));
                    $this->line('   • Medium questions: ' . ($diff['medium'] ?? 0));
                    $this->line('   • Hard questions: ' . ($diff['hard'] ?? 0));
                }
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}