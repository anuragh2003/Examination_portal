<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    /**
     * Student exam access page - Enter details to start exam
     * 
     * @param string $uuid Exam UUID
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function examAccess(string $uuid)
    {
        try {
            $exam = Exam::where('uuid', $uuid)->firstOrFail();
            
            // Check if exam is active
            if ($exam->status !== 'active') {
                return view('student.exam-not-available', [
                    'message' => 'This exam is not currently available.',
                    'exam' => $exam
                ]);
            }
            
            // Check if student already has a session for this exam
            $studentId = session("student_exam_{$uuid}");
            if ($studentId) {
                return redirect()->route('student.exam.take', $uuid);
            }
            
            return view('student.exam-access', compact('exam'));
            
        } catch (\Exception $e) {
            return view('student.exam-not-available', [
                'message' => 'Exam not found or has been removed.',
                'exam' => null
            ]);
        }
    }
    
    /**
     * Start exam - Student enters details and begins
     * 
     * @param Request $request
     * @param string $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startExam(Request $request, string $uuid)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'student_id' => 'nullable|string|max:100'
        ]);
        
        try {
            $exam = Exam::where('uuid', $uuid)->firstOrFail();
            
            // Check if exam is still active
            if ($exam->status !== 'active') {
                return back()->with('error', 'This exam is no longer available.');
            }
            
            // Create student session
            $studentSession = [
                'name' => $request->student_name,
                'email' => $request->student_email,
                'student_id' => $request->student_id,
                'exam_uuid' => $uuid,
                'start_time' => now()->toDateTimeString(),
                'answers' => [],
                'current_question' => 1
            ];
            
            session(["student_exam_{$uuid}" => $studentSession]);
            
            return redirect()->route('student.exam.take', $uuid);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error starting exam. Please try again.');
        }
    }
    
    /**
     * Take exam - Main exam interface
     * 
     * @param string $uuid
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function takeExam(string $uuid)
    {
        try {
            // Check if student has valid session
            $studentSession = session("student_exam_{$uuid}");
            if (!$studentSession) {
                return redirect()->route('student.exam.access', $uuid)
                    ->with('error', 'Please enter your details to start the exam.');
            }
            
            $exam = Exam::where('uuid', $uuid)->firstOrFail();
            
            // TEMPORARY: Disable time check for debugging
            $startTime = \Carbon\Carbon::parse($studentSession['start_time']);
            $currentTime = now();
            
            // Debug: Log time information but don't fail the exam yet
            \Log::info("Exam Time Check (DEBUG MODE)", [
                'exam_uuid' => $uuid,
                'start_time' => $startTime->toDateTimeString(),
                'current_time' => $currentTime->toDateTimeString(),
                'duration_minutes' => $exam->duration_minutes,
                'raw_start_time' => $studentSession['start_time']
            ]);
            
            // Calculate elapsed time for logging only
            if ($currentTime->greaterThanOrEqualTo($startTime)) {
                $timeElapsedSeconds = $startTime->diffInSeconds($currentTime);
                $timeElapsedMinutes = floor($timeElapsedSeconds / 60);
                
                \Log::info("Time Calculation (DEBUG MODE)", [
                    'elapsed_seconds' => $timeElapsedSeconds,
                    'elapsed_minutes' => $timeElapsedMinutes,
                    'exam_duration' => $exam->duration_minutes,
                    'would_expire' => $timeElapsedMinutes >= $exam->duration_minutes
                ]);
            } else {
                \Log::warning("Current time is before start time!", [
                    'start_time' => $startTime->toDateTimeString(),
                    'current_time' => $currentTime->toDateTimeString()
                ]);
            }
            
            // DISABLED FOR DEBUGGING: Don't auto-submit the exam
            // if ($timeElapsedMinutes >= $exam->duration_minutes) {
            //     return redirect()->route('student.exam.submit', $uuid)
            //         ->with('info', 'Exam time has expired. Your answers have been submitted.');
            // }
            
            // Get exam questions
            $questions = $this->getExamQuestions($exam->id);
            
            return view('student.take-exam', compact('exam', 'questions', 'studentSession'));
            
        } catch (\Exception $e) {
            return redirect()->route('student.exam.access', $uuid)
                ->with('error', 'Error loading exam. Please try again.');
        }
    }
    
    /**
     * Save answer - AJAX endpoint for auto-save
     * 
     * @param Request $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function saveAnswer(Request $request, string $uuid): JsonResponse
    {
        try {
            $studentSession = session("student_exam_{$uuid}");
            if (!$studentSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please restart the exam.'
                ], 401);
            }
            
            $questionId = $request->input('question_id');
            $answer = $request->input('answer');
            
            // Update answers in session
            $studentSession['answers'][$questionId] = [
                'answer' => $answer,
                'saved_at' => now()->toISOString(),
                'time_spent' => $request->input('time_spent', 0)
            ];
            
            session(["student_exam_{$uuid}" => $studentSession]);
            
            return response()->json([
                'success' => true,
                'message' => 'Answer saved successfully',
                'saved_at' => now()->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving answer: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit exam - Final submission
     * 
     * @param Request $request
     * @param string $uuid
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function submitExam(Request $request, string $uuid)
    {
        try {
            $studentSession = session("student_exam_{$uuid}");
            if (!$studentSession) {
                return redirect()->route('student.exam.access', $uuid)
                    ->with('error', 'Session not found. Please restart the exam.');
            }
            
            $exam = Exam::where('uuid', $uuid)->firstOrFail();
            
            // Calculate exam results
            $results = $this->calculateResults($exam->id, $studentSession['answers']);
            
            // Store submission (you could save to database here)
            $submission = [
                'student' => [
                    'name' => $studentSession['name'],
                    'email' => $studentSession['email'],
                    'student_id' => $studentSession['student_id']
                ],
                'exam' => [
                    'name' => $exam->name,
                    'uuid' => $exam->uuid,
                    'total_marks' => $exam->total_marks,
                    'duration_minutes' => $exam->duration_minutes
                ],
                'submission' => [
                    'start_time' => $studentSession['start_time'],
                    'submit_time' => now(),
                    'answers' => $studentSession['answers'],
                    'results' => $results
                ]
            ];
            
            // Clear student session
            session()->forget("student_exam_{$uuid}");
            
            return view('student.exam-submitted', compact('submission', 'exam'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error submitting exam: ' . $e->getMessage());
        }
    }
    
    /**
     * Get exam questions with options
     * 
     * @param int $examId
     * @return \Illuminate\Support\Collection
     */
    private function getExamQuestions(int $examId)
    {
        $questions = DB::table('exam_questions')
            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->where('exam_questions.exam_id', $examId)
            ->select(
                'questions.*',
                'exam_questions.order_position'
            )
            ->orderBy('exam_questions.order_position')
            ->get();
        
        // Load options for MCQ questions
        foreach ($questions as $question) {
            if (in_array($question->type, ['mcq_single', 'mcq_multiple'])) {
                $question->options = DB::table('question_options')
                    ->where('question_id', $question->id)
                    ->select('id', 'option_text')  // Don't include is_correct
                    ->get();
            }
        }
        
        return $questions;
    }
    
    /**
     * Calculate exam results
     * 
     * @param int $examId
     * @param array $answers
     * @return array
     */
    private function calculateResults(int $examId, array $answers): array
    {
        $questions = DB::table('exam_questions')
            ->join('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->where('exam_questions.exam_id', $examId)
            ->select('questions.*')
            ->get();
        
        $totalMarks = 0;
        $earnedMarks = 0;
        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;
        
        foreach ($questions as $question) {
            $totalMarks += $question->marks;
            
            if (!isset($answers[$question->id])) {
                $unanswered++;
                continue;
            }
            
            $studentAnswer = $answers[$question->id]['answer'];
            
            if (in_array($question->type, ['mcq_single', 'mcq_multiple'])) {
                // Get correct answers
                $correctAnswers = DB::table('question_options')
                    ->where('question_id', $question->id)
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->toArray();
                
                if ($question->type === 'mcq_single') {
                    if (in_array($studentAnswer, $correctAnswers)) {
                        $correct++;
                        $earnedMarks += $question->marks;
                    } else {
                        $incorrect++;
                    }
                } else { // mcq_multiple
                    $studentAnswerArray = is_array($studentAnswer) ? $studentAnswer : [$studentAnswer];
                    
                    // Perfect match required for multiple choice
                    if (array_diff($correctAnswers, $studentAnswerArray) === [] && 
                        array_diff($studentAnswerArray, $correctAnswers) === []) {
                        $correct++;
                        $earnedMarks += $question->marks;
                    } else {
                        $incorrect++;
                    }
                }
            } else {
                // Descriptive questions - for now, give full marks (manual checking needed)
                if (!empty(trim($studentAnswer))) {
                    $earnedMarks += $question->marks;
                }
            }
        }
        
        return [
            'total_questions' => $questions->count(),
            'answered' => $correct + $incorrect,
            'unanswered' => $unanswered,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'total_marks' => $totalMarks,
            'earned_marks' => $earnedMarks,
            'percentage' => $totalMarks > 0 ? round(($earnedMarks / $totalMarks) * 100, 2) : 0
        ];
    }
    
    /**
     * Get remaining time - AJAX endpoint
     * 
     * @param string $uuid
     * @return JsonResponse
     */
    public function getRemainingTime(string $uuid): JsonResponse
    {
        try {
            $studentSession = session("student_exam_{$uuid}");
            if (!$studentSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }
            
            $exam = Exam::where('uuid', $uuid)->firstOrFail();
            $startTime = \Carbon\Carbon::parse($studentSession['start_time']);
            $currentTime = now();
            
            // Calculate remaining time properly
            $timeElapsedSeconds = 0;
            $remainingSeconds = $exam->duration_minutes * 60;
            
            if ($currentTime->greaterThanOrEqualTo($startTime)) {
                // Use seconds for precise calculation
                $timeElapsedSeconds = $startTime->diffInSeconds($currentTime);
                $timeElapsedMinutes = floor($timeElapsedSeconds / 60);
                $remainingMinutes = max(0, $exam->duration_minutes - $timeElapsedMinutes);
                
                // Also calculate remaining seconds for more accurate display
                $remainingSeconds = max(0, ($exam->duration_minutes * 60) - $timeElapsedSeconds);
            } else {
                // If current time is before start time, full duration is remaining
                $remainingMinutes = $exam->duration_minutes;
            }
            
            return response()->json([
                'success' => true,
                'remaining_minutes' => $remainingMinutes,
                'remaining_seconds' => $remainingSeconds,
                'elapsed_seconds' => $timeElapsedSeconds,
                'time_up' => $remainingMinutes <= 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting time: ' . $e->getMessage()
            ], 500);
        }
    }
}