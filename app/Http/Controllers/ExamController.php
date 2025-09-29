<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ExamController extends Controller
{
    // Show all exams in dashboard
    public function index()
{
   $user = session('user'); // get user from session

    if (!$user) {
        // Redirect to login if not found in session
        return redirect()->route('login')->with('error', 'Please login first.');
    }

    $exams = Exam::latest()->get(); // get all exams
    return view('index', compact('exams', 'user'));
}

    // Show form to create a new exam
    public function create()
    {
        return view('exams.create');
    }

    // Store newly created exam
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'total_marks' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:draft,active,archived'
        ]);

        Exam::create($request->all());

        return redirect()->route('dashboard')->with('success', 'Exam created successfully!');
    }
    

    // Show exam detail (page to import CSV)
    public function show($uuid)
    {
        $exam = Exam::where('uuid', $uuid)->firstOrFail(); // <--- rename to $exam
        return view('exams.show', compact('exam'));        // pass as $exam, not $exams
    }

    // Delete exam
    public function destroy($uuid)
    {
    $exam = Exam::where('uuid', $uuid)->firstOrFail();
    $exam->delete();

    return redirect()->route('dashboard')->with('success', 'Exam deleted successfully!');
    }

}
