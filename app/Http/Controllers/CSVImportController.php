<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Student;
use Illuminate\Support\Str;
use Exception;

class CSVImportController extends Controller
{
    /**
     * Display the CSV import form
     */
    public function showImportForm(string $examUuid)
    {
        $exam = \App\Models\Exam::where('uuid', $examUuid)->firstOrFail();
        return view('admin.csv-import', compact('exam'));
    }

    /**
     * Process CSV file and import questions
     */
    public function import(Request $request, string $examUuid)
    {
        $exam = \App\Models\Exam::where('uuid', $examUuid)->firstOrFail();
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            
            // Read CSV file
            $csvData = array_map('str_getcsv', file($path));
            $header = array_shift($csvData); // Remove header row

            // to check how many qns were successfully imported, skipped, errors
            $importStats = [
                'total_rows' => count($csvData),
                'imported' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction(); //if anything goes wrong, rollback

            foreach ($csvData as $rowIndex => $row) {
                try {
                    $questionData = $this->parseCSVRow($header, $row, $exam->id);
                    
                    if ($this->importQuestion($questionData)) { // returns true if imported, false if duplicate
                        $importStats['imported']++;
                    } else {
                        $importStats['skipped']++;
                    }
                    
                } catch (Exception $e) { 
                    $importStats['errors'][] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    $importStats['skipped']++; //if any row fails, count as skipped
                }
            }

            DB::commit(); //save all changes

            return redirect()->back()->with([
                'success' => 'CSV Import completed successfully!',
                'import_stats' => $importStats
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['csv_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function showStudentImportForm()
    {
        return view('admin.student-import');
    }

    public function importStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $csvData = array_map('str_getcsv', file($path));
            $headerRow = array_shift($csvData);
            $header = array_map(function ($column) {
                return strtolower(trim($column));
            }, $headerRow);

            $required = ['name', 'email', 'mobile', 'role'];
            foreach ($required as $field) {
                if (!in_array($field, $header, true)) {
                    throw new Exception("Missing required column: {$field}");
                }
            }

            $importStats = [
                'total_rows' => count($csvData),
                'imported' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            foreach ($csvData as $rowIndex => $row) {
                try {
                    $row = array_map('trim', $row);
                    $data = array_combine($header, $row);

                    $email = strtolower($data['email'] ?? '');
                    $name = $data['name'] ?? null;
                    $mobile = $data['mobile'] ?? null;
                    $role = $data['role'] ?? null;
                    $city = $data['city'] ?? null;

                    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email address.');
                    }
                    if (!$name) {
                        throw new Exception('Name is required.');
                    }
                    if (!$mobile || !preg_match('/^[0-9]{10,15}$/', $mobile)) {
                        throw new Exception('Invalid mobile number.');
                    }
                    if (!$role) {
                        throw new Exception('Role is required.');
                    }

                    $student = Student::updateOrCreate(
                        ['candidate_email' => $email, 'role' => $role],
                        [
                            'candidate_name' => $name,
                            'candidate_contact' => $mobile,
                            'candidate_city' => $city,
                        ]
                    );

                    if ($student->wasRecentlyCreated || $student->wasChanged()) {
                        $importStats['imported']++;
                    } else {
                        $importStats['skipped']++;
                    }
                } catch (Exception $e) {
                    $importStats['errors'][] = 'Row ' . ($rowIndex + 2) . ': ' . $e->getMessage();
                    $importStats['skipped']++;
                }
            }

            DB::commit();

            return redirect()->route('students.list')->with([
                'success' => 'Student import completed successfully!',
                'import_stats' => $importStats
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('students.list')
                ->withErrors(['csv_file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function listStudents(Request $request)
    {
        $user = session('user'); // get user from session

        if (!$user) {
            // Redirect to login if not found in session
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if ($user->email !== 'admin@email.com') {
            return redirect('/dashboard')->with('error', 'Access denied. Admin only.');
        }

        try {
            // Filter inputs
            $nameFilter = $request->input('nameFilter');
            $emailFilter = $request->input('emailFilter');
            $roleFilter = $request->input('roleFilter');
            $cityFilter = $request->input('cityFilter');
            $statusFilter = $request->input('statusFilter');
            $fromDate = $request->input('fromDateFilter');
            $toDate = $request->input('toDateFilter');

            // Build the base query for all students
            $baseQuery = Student::query()
                ->select(
                    'id',
                    'candidate_name',
                    'candidate_email',
                    'candidate_contact',
                    'candidate_city',
                    'role',
                    'registered_at',
                    'started_at',
                    'submitted_at',
                    'attempt_completed'
                );

            if ($nameFilter) {
                $nameSearch = '%' . strtolower($nameFilter) . '%';
                $baseQuery->whereRaw('LOWER(candidate_name) LIKE ?', [$nameSearch]);
            }

            if ($emailFilter) {
                $emailSearch = '%' . strtolower($emailFilter) . '%';
                $baseQuery->whereRaw('LOWER(candidate_email) LIKE ?', [$emailSearch]);
            }

            if ($roleFilter) {
                $baseQuery->where('role', $roleFilter);
            }

            if ($cityFilter) {
                $citySearch = '%' . strtolower($cityFilter) . '%';
                $baseQuery->whereRaw('LOWER(candidate_city) LIKE ?', [$citySearch]);
            }

            if ($statusFilter === 'completed') {
                $baseQuery->where('attempt_completed', true);
            } elseif ($statusFilter === 'in-progress') {
                $baseQuery->where('attempt_completed', false)->whereNotNull('started_at');
            } elseif ($statusFilter === 'not-started') {
                $baseQuery->whereNull('started_at');
            }

            if ($fromDate) {
                $baseQuery->whereDate('registered_at', '>=', $fromDate);
            }
            if ($toDate) {
                $baseQuery->whereDate('registered_at', '<=', $toDate);
            }

            $baseQuery->orderBy('candidate_name');

            // Pagination: show 10 students per page
            $students = $baseQuery->paginate(10)->withQueryString();

            // Calculate dashboard totals from the full student set
            $allStudents = $baseQuery->get();
            $totalStudents = $allStudents->count();
            $completedStudents = $allStudents->where('attempt_completed', true)->count();
            $inProgressStudents = $allStudents->where('attempt_completed', false)->whereNotNull('started_at')->count();
            $notStartedStudents = $allStudents->whereNull('started_at')->count();

            // Get unique roles and cities for filter dropdowns
            $roles = Student::distinct()->pluck('role')->filter()->sort()->values();
            $cities = Student::distinct()->pluck('candidate_city')->filter()->sort()->values();

            return view('admin.students-list', compact(
                'students',
                'totalStudents',
                'completedStudents',
                'inProgressStudents',
                'notStartedStudents',
                'roles',
                'cities'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to load students: ' . $e->getMessage());
        }
    }

    public function updateStudent(Request $request, $id)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if ($user->email !== 'admin@email.com') {
            return redirect('/dashboard')->with('error', 'Access denied. Admin only.');
        }

        try {
            $student = Student::findOrFail($id);

            $validated = $request->validate([
                'candidate_name' => 'required|string|max:255',
                'candidate_email' => 'required|email|max:255',
                'candidate_contact' => 'required|string|max:20',
                'candidate_city' => 'nullable|string|max:255',
                'role' => 'required|string|max:255'
            ]);

            $student->update($validated);

            return redirect()->route('students.list')->with('success', 'Student updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to update student: ' . $e->getMessage());
        }
    }

    public function deleteStudent($id)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if ($user->email !== 'admin@email.com') {
            return redirect('/dashboard')->with('error', 'Access denied. Admin only.');
        }

        try {
            $student = Student::findOrFail($id);
            $studentName = $student->candidate_name;
            $student->delete();

            return redirect()->route('students.list')->with('success', "Student '{$studentName}' deleted successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV row into question data structure
     */
    private function parseCSVRow($header, $row, int $examId)
    {
        $data = array_combine($header, $row); // combine header and row to associative array
        
        // Required fields validation
        $required = ['question_text', 'type', 'marks']; // question_text, type, marks are mandatory
        foreach ($required as $field) {
            if (empty($data[$field])) { // check if field is missing or empty
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Parse options for MCQ questions
        $options = [];
        if (in_array($data['type'], ['mcq_single', 'mcq_multiple', 'mcq'])) { // MCQ types for options, including 'mcq' as alias
            for ($i = 1; $i <= 6; $i++) { // Support up to 6 options
                $optionKey = "option_{$i}"; // option key
                $correctKey = "correct_{$i}"; // correct answer key

                if (!empty($data[$optionKey])) { // option text is present for this index
                    $options[] = [ // add option to options array
                        'text' => trim($data[$optionKey]),
                        'is_correct' => !empty($data[$correctKey]) && 
                                      strtolower($data[$correctKey]) === 'true' // correct if marked 'true'
                    ];
                }
            }
            
            if (empty($options)) {
                throw new Exception("MCQ question must have at least one option");
            }
        }

        // Map 'mcq' to 'mcq_single' for database compatibility
        $type = $data['type'];
        if ($type === 'mcq') {
            $type = 'mcq_single';
        }

        return [ // return parsed question data
            'text' => trim($data['question_text']),
            'type' => $type,
            'marks' => (int) $data['marks'],
            'difficulty' => $data['difficulty'] ?? 'medium',
            'tags' => $data['tags'] ?? '',
            'status' => $data['status'] ?? 'active',
            'exam_id' => $examId,
            'options' => $options
        ];
    }

    /**
     * Import a single question with its options
     * Allows reuse of questions across exams but prevents duplicates within the same exam
     */
    private function importQuestion($questionData)
    {
        $examId = $questionData['exam_id'];
        
        // Generate import hash based on content only (NOT exam_id) to enable reuse across exams
        $hashData = $questionData['text'] . '|' . 
                   implode('|', array_column($questionData['options'], 'text')) . '|' . 
                   $questionData['marks'] . '|' .
                   $questionData['type'];
        $importHash = hash('sha256', $hashData);

        // Check if question with this content already exists globally
        $existingQuestion = DB::table('questions')
            ->where('import_hash', $importHash)
            ->first();

        if ($existingQuestion) {
            // Question exists, check if it's already linked to this exam
            $alreadyLinked = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->where('question_id', $existingQuestion->id)
                ->exists();

            if ($alreadyLinked) {
                return false; // Skip - already in this exam
            }

            // Link existing question to this exam
            $maxPosition = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->max('order_position') ?? 0;

            DB::table('exam_questions')->insert([
                'exam_id' => $examId,
                'question_id' => $existingQuestion->id,
                'order_position' => $maxPosition + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true; // Successfully linked existing question
        }

        // Create new question (don't store exam_id in questions table)
        $questionId = DB::table('questions')->insertGetId([
            'text' => $questionData['text'],
            'type' => $questionData['type'],
            'marks' => $questionData['marks'],
            'difficulty' => $questionData['difficulty'],
            'tags' => $questionData['tags'],
            'status' => $questionData['status'],
            'import_hash' => $importHash,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Link question to exam via exam_questions
        $maxPosition = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->max('order_position') ?? 0;

        DB::table('exam_questions')->insert([
            'exam_id' => $examId,
            'question_id' => $questionId,
            'order_position' => $maxPosition + 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert options for MCQ questions
        if (!empty($questionData['options'])) {
            $optionsToInsert = [];
            foreach ($questionData['options'] as $option) {
                $optionsToInsert[] = [
                    'question_id' => $questionId,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            DB::table('question_options')->insert($optionsToInsert);
        }

        return true; // Successfully imported new question
    }
}