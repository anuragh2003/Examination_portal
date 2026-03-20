<?php

namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ProctorRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Mail\WelcomeEmail;
use App\Models\student_answer;
use Illuminate\Support\Facades\Log;
use App\Models\ProctorVideo;
use App\Models\ExamInstance;

class StudentController extends Controller
{
public function examAccess(string $uuid)
{
    try {
        $exam = Exam::where('uuid', $uuid)->first();
        $instance = null;
        
        if (!$exam) {
            $instance = ExamInstance::where('uuid', $uuid)->first();
            if ($instance) {
                $exam = $instance->exam;
                session(['exam_instance_uuid' => $uuid]);
            } else {
                return view('student.exam-not-available', [
                    'message' => 'Exam not found.',
                    'exam' => null
                ]);
            }
        }
        
        // Check if exam is active
        if ($exam->status !== 'active') {
            return view('student.exam-not-available', [
                'message' => 'This exam is not currently available.',
                'exam' => $exam
            ]);
        }
        
        if (session()->has('student_email')) {
            $student = Student::where('email', session('student_email'))
                        ->where('exam_id', $exam->id)
                        ->first();

            if ($student) {
                // Check if registered within 1 hour
                if ($student->registered_at && now()->diffInHours($student->registered_at) >= 1) {
                    // Delete old student record
                    $student->delete();
                    session()->forget(['student_email']);
                    return redirect()->route('student.exam.access', $uuid)
                        ->with('error', 'Your registration has expired. Please register again.');
                }
                // Student already registered → redirect to take exam
                return redirect()->route('student.exam.take', $exam->uuid);
            }
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
    
    
     public function register(Request $request)
    {
        // ✅ Validate student input
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email',
            'candidate_contact' => 'nullable|string|max:15',
            'candidate_city' => 'nullable|string|max:255',
        ]);

        // ✅ Generate OTP
        $otp = rand(100000, 999999);
        $existing = Student::where('exam_id', $request->exam_id)
                   ->where('candidate_email', $request->candidate_email)
                   ->first();
if ($existing) {
    $existing->delete();
}

        // ✅ Create student record
        $student = Student::create([
            'exam_id' => $request->exam_id,
            'candidate_name' => $request->candidate_name,
            'candidate_email' => $request->candidate_email,
            'candidate_contact' => $request->candidate_contact,
            'candidate_city' => $request->candidate_city,
            'registered_at' => now(), // Add registration time
        ]);

        // ✅ Store student ID in session for OTP verification
        session(['student_email' => $student->email]);
        Session::put('otp', $otp);
        Session::put('student_id', $student->id);

        // ✅ Get exam for shuffling logic
        $exam = Exam::find($request->exam_id);

        // ✅ Shuffle questions uniquely for this student
        if ($exam) {
            $selector = new \App\Services\QuestionSelector();
            $selectionResult = $selector->selectQuestions($exam->total_marks, [], $exam->id);
            
            if ($selectionResult['success']) {
                $selectedQuestionIds = array_column($selectionResult['questions'], 'id');
                $student->update([
                    'shuffled_question_ids' => json_encode($selectedQuestionIds)
                ]);
            }
        }

        // ✅ Get exam UUID for session key
        $uuid = $exam ? $exam->uuid : null;

        // ✅ Create student session for exam
        $studentSession = [
            'name' => $student->candidate_name,
            'email' => $student->candidate_email,
            'student_id' => $student->id,
            'exam_uuid' => $uuid,
            'start_time' => now()->toDateTimeString(),
            'answers' => [],
            'current_question' => 1
        ];

        if ($uuid) {
            session(["student_exam_{$uuid}" => $studentSession]);
        }

        // ✅ Prepare email details
        $studentDetails = [
            'name' => $student->candidate_name,
            'email' => $student->candidate_email,
            'city' => $student->candidate_city,
            'contact' => $student->candidate_contact,
            'otp' => $otp,
        ];

        // ✅ Send OTP mail
        Mail::to($student->candidate_email)->send(new WelcomeEmail($studentDetails));

        // ✅ Redirect to OTP verification page
        return redirect()->route('verify-otp')
                         ->with('success', 'OTP has been sent to your email!');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $storedOtp = Session::get('otp');

        if ($request->otp == $storedOtp) {
            $studentId = Session::get('student_id');
            $student = Student::find($studentId);

            if ($student instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            // Clear OTP and student_id, but keep exam_id
            Session::forget(['otp', 'student_id']);
                return back()->with('error', 'Student not found or invalid.');
            }

            // Clear OTP and user_id, but keep exam_id
            Session::forget(['otp', 'student_id']);

            // Get exam UUID from student record
            $exam = Exam::find($student->exam_id);
            if (!$exam) {
                return back()->with('error', 'Exam not found.');
            }
            return redirect()->route('student.exam.take', $exam->uuid)->with('success', 'Verified! Start your exam.');
        } else {
            return back()->with('error', 'Invalid OTP. Try again.');
        }
    }
     public function showVerifyForm()
    {
        return view('verify-otp');  // We'll create this view
    }
public function takeExam(string $uuid)
{
    try {
        $exam = Exam::where('uuid', $uuid)->first();
        $instance = null;
        
        if (!$exam) {
            $instance = ExamInstance::where('uuid', $uuid)->first();
            if ($instance) {
                $exam = $instance->exam;
                session(['exam_instance_uuid' => $uuid]);
            } else {
                return redirect()->route('student.exam.access', $uuid)
                    ->with('error', 'Exam not found.');
            }
        }
        
        // Check if student has valid session
        $studentSession = session("student_exam_{$uuid}");
        if (!$studentSession) {
            return redirect()->route('student.exam.access', $uuid)
                ->with('error', 'Please enter your details to start the exam.');
        }

        // Ensure student_id is in session for getExamQuestions
        Session::put('student_id', $studentSession['student_id']);

        // Check if exam has already been submitted by this student
        $existingSubmission = DB::table('student_answers')
            ->where('exam_id', $exam->id)
            ->where('student_id', $studentSession['student_id'])
            ->exists();

        if ($existingSubmission) {
            return redirect()->route('student.exam-submitted', $uuid)
                ->with('info', 'You have already submitted this exam.');
        }
        
        // TEMPORARY: Disable time check for debugging
        $startTime = \Carbon\Carbon::parse($studentSession['start_time']);
        $currentTime = now();
        
        // Debug: Log time information but don't fail the exam yet
        Log::info("Exam Time Check (DEBUG MODE)", [
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
            
            Log::info("Time Calculation (DEBUG MODE)", [
                'elapsed_seconds' => $timeElapsedSeconds,
                'elapsed_minutes' => $timeElapsedMinutes,
                'exam_duration' => $exam->duration_minutes,
                'would_expire' => $timeElapsedMinutes >= $exam->duration_minutes
            ]);
        } else {
            Log::warning("Current time is before start time!", [
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
        $questions = $this->getExamQuestions($exam->id, session('exam_instance_uuid'));
        
        return view('student.take-exam', compact('exam', 'questions', 'studentSession'));
        
    } catch (\Exception $e) {
        return redirect()->route('student.exam.access', $uuid)
            ->with('error', 'Error loading exam. Please try again.');
    }
}
    
public function submitExam(Request $request, $uuid)
{
    $studentSession = session("student_exam_{$uuid}");
    $exam = Exam::where('uuid', $uuid)->firstOrFail();
    $examId = $exam->id;

    // Log auto-submit reason if present
    if ($request->has('auto_submit_reason')) {
        Log::info('Auto-submit triggered', [
            'reason' => $request->input('auto_submit_reason'),
            'exam_uuid' => $uuid,
            'student_id' => $studentSession['student_id'] ?? 'unknown'
        ]);
    }

    // ✅ Make sure we always have student_id
    $studentId = $studentSession['student_id'] ?? Session::get('student_id');

    if (!$studentId) {
        return redirect()->route('student.exam.access', $uuid)
            ->with('error', 'Student session expired. Please re-register.');
    }

    // Get all exam questions
    $allQuestions = DB::table('exam_questions')
        ->where('exam_id', $examId)
        ->pluck('question_id')
        ->toArray();

    $submittedAnswers = $request->input('answers', []);
    $finalAnswers = [];

    foreach ($allQuestions as $questionId) {
        $answerData = $submittedAnswers[$questionId] ?? [];
        $answerText = $answerData['answer_text'] ?? null;
        $chosenOptionIds = $answerData['chosen_option_ids'] ?? null;

        if (is_array($chosenOptionIds)) {
            $chosenOptionIds = json_encode($chosenOptionIds);
        }

        // Get question details
        $question = DB::table('questions')->where('id', $questionId)->first();
        $awardedMarks = 0;
        $status = 'pending';

        if (in_array($question->type, ['mcq_single', 'mcq_multiple', 'mcq'])) {
            // For MCQ, check if answers are correct
            $correctOptions = DB::table('question_options')
                ->where('question_id', $questionId)
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            // Handle student options: could be JSON array or single value
            if (is_array($chosenOptionIds)) {
                $studentOptions = $chosenOptionIds;
            } else {
                $decoded = json_decode($chosenOptionIds, true);
                $studentOptions = is_array($decoded) ? $decoded : (is_numeric($chosenOptionIds) ? [$chosenOptionIds] : []);
            }

            sort($correctOptions);
            sort($studentOptions);

            if ($correctOptions == $studentOptions) {
                $awardedMarks = $question->marks;
                $status = 'approved';
            } else {
                $status = 'rejected';
            }

            // Ensure chosen_option_ids is stored as JSON
            $chosenOptionIds = json_encode($studentOptions);
        } else {
            // Descriptive: pending for admin approval
            $status = 'pending';
            $chosenOptionIds = null; // No options for descriptive
        }

        // ✅ Save answer with status and marks
        student_answer::updateOrCreate(
            [
                'exam_id' => $examId,
                'question_id' => $questionId,
                'student_id' => $studentId,
            ],
            [
                'answer_text' => $answerText,
                'chosen_option_ids' => $chosenOptionIds,
                'status' => $status,
                'awarded_marks' => $awardedMarks
            ]
        );

        $finalAnswers[$questionId] = [
            'answer_text' => $answerText,
            'chosen_option_ids' => $answerData['chosen_option_ids'] ?? [],
            'awarded_marks' => $awardedMarks,
            'status' => $status
        ];
    }

    // Calculate total marks
    $totalMarks = array_sum(array_column($finalAnswers, 'awarded_marks'));

    // Store submission summary
    $summary = [
        'exam_name' => $exam->name,
        'student_name' => $studentSession['name'] ?? 'N/A',
        'student_email' => $studentSession['email'] ?? 'N/A',
        'submitted_at' => now()->toDateTimeString(),
        'total_questions' => count($allQuestions),
        'attempted' => count(array_filter($finalAnswers, function ($ans) {
            return !empty($ans['answer_text']) || !empty($ans['chosen_option_ids']);
        })),
        'unattempted' => count($allQuestions) - count(array_filter($finalAnswers, function ($ans) {
            return !empty($ans['answer_text']) || !empty($ans['chosen_option_ids']);
        })),
        'total_marks' => $totalMarks,
        'answers' => $finalAnswers
    ];

    // Store submission summary in session
    Session::put("exam_summary_{$uuid}", $summary);

    // Store student_id in session for the submitted page
    Session::put('student_id', $studentId);

    // ✅ clear session but keep student record (and their answers) for review/audit
    Session::forget(["student_exam_{$uuid}", 'student_email']);

    // Check if this is an AJAX request
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Exam submitted successfully',
            'summary' => $summary
        ]);
    }

    return view('student.exam-submitted', [
        'summary' => $summary
    ]);
}

public function examSubmitted($uuid)
{
    $exam = Exam::where('uuid', $uuid)->firstOrFail();
    $studentId = session('student_id');

    if (!$studentId) {
        return redirect()->route('student.exam.access', $uuid)->with('error', 'Session expired. Please re-register to view results.');
    }

    $summary = session("exam_summary_{$uuid}");

    if ($summary) {
        // Update total_marks with current awarded marks (including approvals)
        $currentTotalMarks = DB::table('student_answers')
            ->where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->sum('awarded_marks');

        $summary['total_marks'] = $currentTotalMarks;
    } else {
        // Fallback: create basic summary
        $currentTotalMarks = DB::table('student_answers')
            ->where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->sum('awarded_marks');

        $summary = [
            'exam_name' => $exam->name,
            'student_name' => 'N/A', // Could fetch from students table if needed
            'student_email' => 'N/A',
            'submitted_at' => now()->toDateTimeString(),
            'total_questions' => DB::table('exam_questions')->where('exam_id', $exam->id)->count(),
            'attempted' => 0, // Not calculating full summary
            'unattempted' => 0,
            'total_marks' => $currentTotalMarks,
            'answers' => []
        ];
    }

    return view('student.exam-submitted', [
        'uuid' => $uuid,
        'summary' => $summary,
    ]);
}



public function uploadProctorVideos(Request $request)
{
    $request->validate([
        'camera_video' => 'nullable|mimes:webm,mp4,mov|max:51200',
        'screen_video' => 'nullable|mimes:webm,mp4,mov|max:51200',
        'student_id' => 'required|exists:students,id',
        'exam_id' => 'required|exists:exams,id',
    ]);

    $uploaded = [];
    $errors = [];
    $studentId = $request->student_id;
    $examId = $request->exam_id;

    $videos = [
        'camera_video' => 'camera',
        'screen_video' => 'screen',
    ];

    // Create destination folder with proper permissions
    $destination = storage_path('app/public/proctor_videos/');
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }

    foreach ($videos as $inputName => $type) {
        if ($request->hasFile($inputName)) {
            $file = $request->file($inputName);
            
            // Create unique filename to avoid conflicts
            $randomStr = uniqid();
            $filename = time() . '_' . $randomStr . '_' . $type . '.' . $file->getClientOriginalExtension();
            $fullPath = $destination . $filename;

            try {
                // Move file to destination
                $file->move($destination, $filename);
                
                Log::info("Proctor video file moved", [
                    'filename' => $filename,
                    'type' => $type,
                    'exists' => file_exists($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0
                ]);

                // Verify file was actually saved
                if (!file_exists($fullPath)) {
                    throw new \Exception("File was not saved to disk: $fullPath");
                }

                $fileSize = filesize($fullPath);
                if ($fileSize === 0) {
                    throw new \Exception("Saved file is empty: $fullPath");
                }

                // Save to database
                $video = ProctorVideo::create([
                    'student_id' => $studentId,
                    'exam_id' => $examId,
                    'type' => $type,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $fileSize,
                ]);

                $uploaded[] = [
                    'id' => $video->id,
                    'type' => $type,
                    'filename' => $filename,
                    'size' => $fileSize
                ];

                Log::info("Proctor video saved to database", [
                    'video_id' => $video->id,
                    'type' => $type,
                    'filename' => $filename,
                    'size' => $fileSize
                ]);

            } catch (\Exception $e) {
                $errorMsg = "Failed to save $type video: " . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error($errorMsg, [
                    'type' => $type,
                    'filename' => $filename ?? 'unknown',
                    'exception' => $e
                ]);
                // Continue processing other videos instead of returning immediately
            }
        }
    }

    // Return success if at least one video was uploaded
    $hasUploadedVideos = !empty($uploaded);
    
    return response()->json([
        'success' => $hasUploadedVideos,
        'uploaded' => $uploaded,
        'errors' => $errors,
        'message' => $hasUploadedVideos 
            ? 'Videos uploaded successfully' 
            : 'No videos uploaded. Errors: ' . implode(', ', $errors)
    ], $hasUploadedVideos ? 200 : 400);
}


private function getExamQuestions(int $examId, ?string $instanceUuid = null)
{
    // Get student from session
    $studentId = session('student_id');
    $student = $studentId ? Student::find($studentId) : null;
    
    if ($student && $student->shuffled_question_ids) {
        // Use student's shuffled questions
        $shuffledIds = json_decode($student->shuffled_question_ids, true);
        $questions = DB::table('questions')
            ->whereIn('id', $shuffledIds)
            ->orderByRaw("FIELD(id, " . implode(',', $shuffledIds) . ")")
            ->get();
    } elseif ($instanceUuid) {
        $instance = ExamInstance::where('uuid', $instanceUuid)->first();
        if ($instance && $instance->shuffled_question_ids) {
            $shuffledIds = json_decode($instance->shuffled_question_ids, true);
            $questions = DB::table('questions')
                ->whereIn('id', $shuffledIds)
                ->orderByRaw("FIELD(id, " . implode(',', $shuffledIds) . ")")
                ->get();
        } else {
            // Fallback to fixed questions
            $questions = DB::table('exam_questions')
                ->leftJoin('questions', 'exam_questions.question_id', '=', 'questions.id')
                ->where('exam_questions.exam_id', $examId)
                ->select('questions.*', 'exam_questions.order_position')
                ->orderBy('exam_questions.order_position')
                ->get();
        }
    } else {
        $questions = DB::table('exam_questions')
            ->leftJoin('questions', 'exam_questions.question_id', '=', 'questions.id')
            ->where('exam_questions.exam_id', $examId)
            ->select('questions.*', 'exam_questions.order_position')
            ->orderBy('exam_questions.order_position')
            ->get();
    }
    
    // Load options for MCQ questions
    foreach ($questions as $question) {
        if (in_array($question->type, ['mcq_single', 'mcq_multiple'])) {
            $question->options = DB::table('question_options')
                ->where('question_id', $question->id)
                ->select('id', 'option_text')
                ->get();
        }
    }
    
    return $questions;
}
    
}