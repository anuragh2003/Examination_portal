<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            'exam_id' => null, // Don't store exam_id here, use pivot table instead
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