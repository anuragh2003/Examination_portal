<?php

namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentQuestionOrder;
use App\Models\StudentAnswerOption;
use App\Models\ProctorRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Mail\WelcomeEmail;
use App\Models\student_answer;
use Illuminate\Support\Facades\Log;
use App\Models\ProctorScreenshot;
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

        if ($exam->status !== 'active') {
            return view('student.exam-not-available', [
                'message' => 'This exam is not currently available.',
                'exam' => $exam
            ]);
        }

        $studentSession = session("student_exam_{$uuid}");
        $studentId = $studentSession['student_id'] ?? null;

        if ($studentId) {
            $hasSubmitted = DB::table('student_answers')
                ->where('exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->whereNotNull('submitted_at')
                ->exists();

            if ($hasSubmitted) {
                return redirect()->route('student.exam-submitted', $uuid)
                    ->with('info', 'You have already submitted this exam.');
            }
        }

        if ($studentSession) {
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
    $request->validate([
        'exam_id' => 'required|exists:exams,id',
        'candidate_email' => 'required|email|max:255',
    ], [
        'candidate_email.email' => 'Please enter a valid email address.',
    ]);

    $exam = Exam::findOrFail($request->exam_id);

    $student = Student::where('candidate_email', strtolower($request->candidate_email))
        ->where('role', $exam->role)
        ->first();

    if (!$student) {
        return redirect()->route('student.exam.access', $exam->uuid)
            ->with('error', 'This email is not registered for the role required by this exam.');
    }

    $hasSubmittedThisExam = DB::table('student_answers')
        ->where('exam_id', $exam->id)
        ->where('student_id', $student->id)
        ->whereNotNull('submitted_at')
        ->exists();

    if ($hasSubmittedThisExam) {
        return redirect()->route('student.exam.access', $exam->uuid)
            ->with('error', 'You have already completed this exam.');
    }

    if ($student->active_session && $student->active_session_expires_at && now()->lessThan($student->active_session_expires_at)) {
        return redirect()->route('student.exam.access', $exam->uuid)
            ->with('error', 'This email is already writing the exam. Please wait until the current session ends.');
    }

    $otp = rand(100000, 999999);

    $student->update([
        'exam_id' => $student->exam_id ?? $exam->id,
        'otp' => $otp,
        'otp_expires_at' => now()->addMinutes(30),
        'registered_at' => now(),
    ]);

    session(['student_email' => $student->candidate_email]);
    Session::put('otp', $otp);
    Session::put('student_id', $student->id);
    Session::put('student_exam_uuid', $exam->uuid);
    Session::put('student_exam_id', $exam->id);

    if (StudentQuestionOrder::where('student_id', $student->id)->count() === 0) {
        $selector = new \App\Services\QuestionSelector();
        $selectionResult = $selector->selectQuestions($exam->total_marks, [], $exam->id);

        if (!empty($selectionResult['success']) && $selectionResult['success']) {
            $selectedQuestionIds = array_column($selectionResult['questions'], 'id');
            foreach ($selectedQuestionIds as $order => $questionId) {
                StudentQuestionOrder::create([
                    'student_id' => $student->id,
                    'question_id' => $questionId,
                    'order_position' => $order + 1,
                ]);
            }
        }
    }

    $studentDetails = [
        'name' => $student->candidate_name,
        'email' => $student->candidate_email,
        'city' => $student->candidate_city,
        'contact' => $student->candidate_contact,
        'otp' => $otp,
    ];

    Mail::to($student->candidate_email)->send(new WelcomeEmail($studentDetails));

    return redirect()->route('verify.form')
        ->with('success', 'OTP has been sent to your registered email.');
}

public function verifyOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    $storedOtp = Session::get('otp');
    $studentId = Session::get('student_id');
    $examUuid = Session::get('student_exam_uuid');
    $examId = Session::get('student_exam_id');

    if (!$studentId || !$examUuid || !$examId) {
        return redirect()->route('login')->with('error', 'Session expired. Please login again.');
    }

    if ((int)$request->otp !== $storedOtp) {
        return back()->with('error', 'Invalid OTP. Try again.');
    }

    $student = Student::find($studentId);
    if (!$student) {
        return back()->with('error', 'Student not found. Please login again.');
    }

    if ($student->otp_expires_at && now()->greaterThan($student->otp_expires_at)) {
        return back()->with('error', 'OTP has expired. Please request a new one.');
    }

    if ($student->attempt_completed || $student->submitted_at) {
        return redirect()->route('student.exam.access', $examUuid)
            ->with('error', 'You have already completed this exam.');
    }

    $sessionToken = Str::random(40);
    $expiresAt = now()->addMinutes(Exam::findOrFail($examId)->duration_minutes + 5);

    $student->update([
        'active_session' => true,
        'active_session_started_at' => now(),
        'active_session_expires_at' => $expiresAt,
        'session_token' => $sessionToken,
        'otp' => null,
        'otp_expires_at' => null,
        'started_at' => $student->started_at ?? now(),
    ]);

    $studentSession = [
        'name' => $student->candidate_name,
        'email' => $student->candidate_email,
        'student_id' => $student->id,
        'exam_uuid' => $examUuid,
        'exam_id' => $examId,
        'start_time' => now()->toDateTimeString(),
        'session_token' => $sessionToken,
        'answers' => [],
        'current_question' => 1,
    ];

    session(["student_exam_{$examUuid}" => $studentSession]);
    session(['student_session_token' => $sessionToken]);
    Session::forget(['otp']);

    return redirect()->route('student.exam.take', $examUuid)
        ->with('success', 'OTP verified. You may now start the exam.');
}

public function showVerifyForm()
{
    return view('verify-otp');
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

        $student = Student::find($studentSession['student_id']);
        if (!$student) {
            return redirect()->route('student.exam.access', $uuid)
                ->with('error', 'Student session invalid. Please login again.');
        }

        if (empty($studentSession['session_token']) || $student->session_token !== $studentSession['session_token']) {
            return redirect()->route('student.exam.access', $uuid)
                ->with('error', 'Your exam session is no longer valid. Please login again.');
        }

        $hasSubmittedThisExam = DB::table('student_answers')
            ->where('exam_id', $exam->id)
            ->where('student_id', $studentSession['student_id'])
            ->whereNotNull('submitted_at')
            ->exists();

        if ($hasSubmittedThisExam) {
            return redirect()->route('student.exam-submitted', $uuid)
                ->with('info', 'You have already submitted this exam.');
        }

        if ($student->active_session && $student->active_session_expires_at && now()->greaterThan($student->active_session_expires_at)) {
            $student->update([
                'active_session' => false,
                'session_token' => null,
                'active_session_started_at' => null,
                'active_session_expires_at' => null,
            ]);
        }

        if ($student && !$student->started_at) {
            $student->update(['started_at' => now()]);
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

    // Get student's specific questions from student_question_orders
    $studentQuestionIds = StudentQuestionOrder::where('student_id', $studentId)
        ->orderBy('order_position')
        ->pluck('question_id')
        ->toArray();

    $submittedAnswers = $request->input('answers', []);
    $finalAnswers = [];

    foreach ($studentQuestionIds as $questionId) {
        $answerData = $submittedAnswers[$questionId] ?? [];
        $answerText = $answerData['answer_text'] ?? null;
        $chosenOptionIds = $answerData['chosen_option_ids'] ?? null;

        // Handle chosen_option_ids - could be single value or array
        if (is_array($chosenOptionIds)) {
            $studentOptions = array_filter(array_map('intval', $chosenOptionIds));
        } elseif (is_numeric($chosenOptionIds)) {
            $studentOptions = [(int) $chosenOptionIds];
        } else {
            $studentOptions = [];
        }

        // Get question details
        $question = DB::table('questions')->where('id', $questionId)->first();
        $awardedMarks = 0;
        $status = 'pending';

        if ($question && in_array($question->type, ['mcq_single', 'mcq_multiple', 'mcq'])) {
            // For MCQ, check if answers are correct
            $correctOptions = DB::table('question_options')
                ->where('question_id', $questionId)
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            // Convert to arrays of integers and sort for comparison
            $correctOptions = array_map('intval', $correctOptions);
            $studentOptions = array_map('intval', $studentOptions);
            sort($correctOptions);
            sort($studentOptions);

            if (!empty($studentOptions) && $correctOptions == $studentOptions) {
                $awardedMarks = (int) $question->marks;
                $status = 'approved';
            } elseif (!empty($studentOptions)) {
                $status = 'rejected';
                $awardedMarks = 0;
            } else {
                $status = 'pending'; // Not attempted
                $awardedMarks = 0;
            }
        } else {
            // Descriptive: pending for admin approval
            $status = 'pending';
            $awardedMarks = 0;
        }

        // ✅ Save answer with status and marks
        $studentAnswer = student_answer::updateOrCreate(
            [
                'exam_id' => $examId,
                'question_id' => $questionId,
                'student_id' => $studentId,
            ],
            [
                'answer_text' => $answerText,
                'status' => $status,
                'awarded_marks' => $awardedMarks,
                'submitted_at' => now()
            ]
        );

        // Store selected options in normalized table for MCQ
        if (in_array($question->type, ['mcq_single', 'mcq_multiple', 'mcq']) && !empty($studentOptions)) {
            // Replace existing answer options if any
            StudentAnswerOption::where('student_answer_id', $studentAnswer->id)->delete();

            foreach ($studentOptions as $optionId) {
                if (!empty($optionId) && is_numeric($optionId)) {
                    StudentAnswerOption::create([
                        'student_answer_id' => $studentAnswer->id,
                        'question_option_id' => (int) $optionId,
                    ]);
                }
            }
        }

        $finalAnswers[$questionId] = [
            'answer_text' => $answerText,
            'chosen_option_ids' => $studentOptions,
            'awarded_marks' => $awardedMarks,
            'status' => $status
        ];
    }

    // Calculate total marks
    $totalMarks = array_sum(array_column($finalAnswers, 'awarded_marks'));

    // Fetch student from database for accurate info
    $student = Student::find($studentId);
    
    // Calculate time taken using session start_time, fallback to student.started_at, then now.
    if (!empty($studentSession['start_time'])) {
        $startTime = \Carbon\Carbon::parse($studentSession['start_time']);
    } elseif ($student && !empty($student->started_at)) {
        $startTime = \Carbon\Carbon::parse($student->started_at);
    } else {
        $startTime = now();
    }

    $submittedAt = now();
    $timeTakenSeconds = $startTime->diffInSeconds($submittedAt);
    $timeTakenMinutes = floor($timeTakenSeconds / 60);
    $timeTakenRemainingSeconds = $timeTakenSeconds % 60;
    
    // Store submission summary
    $summary = [
        'exam_name' => $exam->name,
        'student_name' => $student ? $student->candidate_name : ($studentSession['name'] ?? 'N/A'),
        'student_email' => $student ? $student->candidate_email : ($studentSession['email'] ?? 'N/A'),
        'submitted_at' => $submittedAt->toDateTimeString(),
        'time_taken_seconds' => $timeTakenSeconds,
        'time_taken_formatted' => sprintf('%d minutes %d seconds', $timeTakenMinutes, $timeTakenRemainingSeconds),
        'total_questions' => count($studentQuestionIds),
        'attempted' => count(array_filter($finalAnswers, function ($ans) {
            return !empty($ans['answer_text']) || !empty($ans['chosen_option_ids']);
        })),
        'unattempted' => count($studentQuestionIds) - count(array_filter($finalAnswers, function ($ans) {
            return !empty($ans['answer_text']) || !empty($ans['chosen_option_ids']);
        })),
        'total_marks' => $totalMarks,
        'answers' => $finalAnswers
    ];

    // Store submission summary in session
    Session::put("exam_summary_{$uuid}", $summary);

    // Store student_id in session for the submitted page
    Session::put('student_id', $studentId);

    $student = Student::find($studentId);
    if ($student) {
        $student->update([
            'active_session' => false,
            'session_token' => null,
            'active_session_started_at' => null,
            'active_session_expires_at' => null,
            'submitted_at' => now(),
            'attempt_completed' => true,
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    // ✅ clear exam-session state but keep student_id for submitted page review
    Session::forget(["student_exam_{$uuid}", 'student_email', 'student_session_token', 'student_exam_uuid', 'student_exam_id']);

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

public function checkSubmission($uuid)
{
    try {
        Log::info("CheckSubmission called for UUID: {$uuid}");
        
        $exam = Exam::where('uuid', $uuid)->first();
        if (!$exam) {
            Log::info("Exam not found for UUID: {$uuid}");
            return response()->json(['submitted' => false, 'error' => 'Exam not found']);
        }

        $studentSession = session("student_exam_{$uuid}");
        $studentId = $studentSession['student_id'] ?? session('student_id');
        
        Log::info("Student session for exam {$uuid}:", ['session' => $studentSession, 'student_id' => $studentId]);

        if (!$studentId) {
            Log::info("No student ID in session for exam {$uuid}");
            return response()->json(['submitted' => false, 'error' => 'No active session']);
        }

        $hasSubmitted = DB::table('student_answers')
            ->where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->whereNotNull('submitted_at')
            ->exists();
        
        Log::info("Submission check result for exam {$exam->id}, student {$studentId}: " . ($hasSubmitted ? 'SUBMITTED' : 'NOT SUBMITTED'));

        return response()->json(['submitted' => $hasSubmitted]);
    } catch (\Exception $e) {
        Log::error("Error in checkSubmission: " . $e->getMessage());
        return response()->json(['submitted' => false, 'error' => 'Server error']);
    }
}

public function uploadProctorScreenshots(Request $request)
{
    Log::info('uploadProctorScreenshots ENTRY POINT', [
        'method' => $request->getMethod(),
        'content_length' => request()->header('Content-Length'),
        'content_type' => $request->header('Content-Type'),
        'has_csrf' => $request->header('X-CSRF-TOKEN') ? 'yes' : 'no',
        'student_id_from_request' => $request->input('student_id'),
        'exam_id_from_request' => $request->input('exam_id'),
        'screenshots_array_exists' => $request->has('screenshots') ? 'yes' : 'no',
        'screenshots_count' => is_array($request->input('screenshots')) ? count($request->input('screenshots')) : 0,
        'files_count' => count($request->files->get('screenshots', [])),
        'all_input_keys' => array_keys($request->all())
    ]);

    try {
        $request->validate([
            'screenshots' => 'required|array|min:1',
            'screenshots.*.image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'screenshots.*.type' => 'required|in:screen,face',
            'screenshots.*.frame_number' => 'required|integer|min:0',
            'screenshots.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
            'student_id' => 'required|exists:students,id',
            'exam_id' => 'required|exists:exams,id',
        ]);
    } catch (\Illuminate\Validation\ValidationException $ex) {
        Log::error('uploadProctorScreenshots validation failed', [
            'errors' => $ex->errors(),
            'input_keys' => array_keys($request->all()),
            'files' => $request->allFiles()
        ]);

        return response()->json([
            'success' => false,
            'errors' => $ex->errors(),
            'message' => 'Validation failed'
        ], 422);
    }

    $uploaded = [];
    $errors = [];
    $studentId = $request->student_id;
    $examId = $request->exam_id;
    $screenshots = $request->input('screenshots');

    $baseDestination = storage_path('app/public/proctor_screenshots/');
    $screenDestination = $baseDestination . 'screen/';
    $faceDestination = $baseDestination . 'face/';

    foreach ([$screenDestination, $faceDestination] as $dest) {
        if (!file_exists($dest)) {
            mkdir($dest, 0777, true);
        }
    }

    foreach ($screenshots as $index => $screenshotData) {
        try {
            if (!$request->hasFile("screenshots.{$index}.image")) {
                throw new \Exception("No image file provided for item {$index}");
            }

            $file = $request->file("screenshots.{$index}.image");
            $type = $screenshotData['type'];
            $frameNumber = (int)$screenshotData['frame_number'];
            $timestamp = $screenshotData['timestamp'];
            $destination = $type === 'face' ? $faceDestination : $screenDestination;

            $randomStr = uniqid();
            $filename = time() . '_' . $randomStr . '_' . $type . '_frame_' . $frameNumber . '.' . $file->getClientOriginalExtension();
            $fullPath = $destination . $filename;

            $file->move($destination, $filename);

            if (!file_exists($fullPath)) {
                throw new \Exception("File was not saved to disk");
            }

            $fileSize = filesize($fullPath);

            $screenshot = ProctorScreenshot::create([
                'student_id' => $studentId,
                'exam_id' => $examId,
                'frame_type' => $type,
                'frame_number' => $frameNumber,
                'timestamp' => $timestamp,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $fileSize,
            ]);

            $uploaded[] = [
                'id' => $screenshot->id,
                'type' => $type,
                'frame_number' => $frameNumber,
                'filename' => $filename
            ];

            Log::info("Proctor screenshot saved", [
                'screenshot_id' => $screenshot->id,
                'student_id' => $studentId,
                'exam_id' => $examId,
                'type' => $type,
                'frame_number' => $frameNumber,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            $errorMsg = "Failed to save screenshot {$index}: " . $e->getMessage();
            $errors[] = $errorMsg;
            Log::error($errorMsg, ['stack' => $e->getTraceAsString()]);
        }
    }

    $hasUploadedScreenshots = !empty($uploaded);

    Log::info('uploadProctorScreenshots SUCCESS', [
        'uploaded_count' => count($uploaded),
        'error_count' => count($errors),
        'student_id' => $studentId,
        'exam_id' => $examId
    ]);

    return response()->json([
        'success' => $hasUploadedScreenshots,
        'uploaded_count' => count($uploaded),
        'uploaded' => $uploaded,
        'errors' => $errors,
        'message' => $hasUploadedScreenshots ? count($uploaded) . ' screenshot(s) uploaded successfully' : 'No screenshots uploaded'
    ], $hasUploadedScreenshots ? 200 : 400);
}


private function getExamQuestions(int $examId, ?string $instanceUuid = null)
{
    // Get student from session
    $studentId = session('student_id');

    $questions = collect();

    // 1) If student has question orders, use that exact sequence
    if ($studentId) {
        $orderedQuestionIds = StudentQuestionOrder::where('student_id', $studentId)
            ->orderBy('order_position')
            ->pluck('question_id')
            ->toArray();

        if (!empty($orderedQuestionIds)) {
            $questions = DB::table('questions')
                ->whereIn('id', $orderedQuestionIds)
                ->orderByRaw("FIELD(id, " . implode(',', $orderedQuestionIds) . ")")
                ->get();
        }
    }

    // 2) If we have an exam instance shuffle and no student orders, use instance values
    if ($questions->isEmpty() && $instanceUuid) {
        $instance = ExamInstance::where('uuid', $instanceUuid)->first();
        if ($instance && $instance->shuffled_question_ids) {
            $shuffledIds = json_decode($instance->shuffled_question_ids, true);
            if (!empty($shuffledIds)) {
                $questions = DB::table('questions')
                    ->whereIn('id', $shuffledIds)
                    ->orderByRaw("FIELD(id, " . implode(',', $shuffledIds) . ")")
                    ->get();
            }
        }
    }

    // 3) If still empty, fallback to exam_questions list (default full exam set)
    if ($questions->isEmpty()) {
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