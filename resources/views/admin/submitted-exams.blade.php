<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submitted Exams</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <div id="pending-data" data-pending="{{ json_encode($pendingAnswers ?? []) }}" style="display: none;"></div>
    <script>
        // Pass PHP data to JavaScript
        window.pendingAnswers = JSON.parse(document.getElementById('pending-data').getAttribute('data-pending') || '{}');
    </script>
</head>
<body class="min-h-screen p-4 sm:p-8">

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">📋 Submitted Exams</h1>
        <p class="text-gray-600">View student submissions, marks, and approve descriptive answers</p>
    </div>

    <!-- Filter & Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold">Total Submissions</p>
            <p class="text-3xl font-bold text-blue-600">{{ $submissions->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm font-semibold">Pending Review</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $submissions->where('needs_review', true)->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold">Fully Approved</p>
            <p class="text-3xl font-bold text-green-600">{{ $submissions->where('fully_approved', true)->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm font-semibold">Exams</p>
            <p class="text-3xl font-bold text-purple-600">{{ $exams->count() }}</p>
        </div>
    </div>

    <!-- Filter by Exam -->
    <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Exam:</label>
        <select id="examFilter" class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Exams</option>
            @foreach($exams as $exam)
                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Student</th>
                        <th class="px-6 py-4 text-left font-semibold">Exam</th>
                        <th class="px-6 py-4 text-left font-semibold">Submitted At</th>
                        <th class="px-6 py-4 text-center font-semibold">Duration</th>
                        <th class="px-6 py-4 text-center font-semibold">MCQ Marks</th>
                        <th class="px-6 py-4 text-center font-semibold">Total Marks</th>
                        <th class="px-6 py-4 text-center font-semibold">Pending</th>
                        <th class="px-6 py-4 text-center font-semibold">Status</th>
                        <th class="px-6 py-4 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($submissions as $submission)
                        <tr class="hover:bg-gray-50 transition" data-exam-id="{{ $submission->exam_id }}">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $submission->student_name }}</div>
                                <div class="text-sm text-gray-500">{{ $submission->student_email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-medium">{{ $submission->exam_name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($submission->submitted_at)->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($submission->started_at && $submission->submitted_at)
                                    @php
                                        $duration = \Carbon\Carbon::parse($submission->started_at)->diff(\Carbon\Carbon::parse($submission->submitted_at));
                                        $hours = $duration->h;
                                        $minutes = $duration->i;
                                        $seconds = $duration->s;
                                        $timeUsedSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                                        $examDurationSeconds = ($submission->exam_duration_minutes ?? 0) * 60;
                                        $timeLeftSeconds = max($examDurationSeconds - $timeUsedSeconds, 0);
                                        $leftHours = intdiv($timeLeftSeconds, 3600);
                                        $leftMinutes = intdiv($timeLeftSeconds % 3600, 60);
                                        $leftSeconds = $timeLeftSeconds % 60;
                                    @endphp
                                    <div class="text-center">
                                        <div class="inline-block bg-orange-100 px-3 py-1 rounded-lg border border-orange-200">
                                            <div class="text-orange-800 font-semibold text-sm">
                                                @if($hours > 0)
                                                    {{ $hours }}h {{ $minutes }}m {{ $seconds }}s
                                                @else
                                                    {{ $minutes }}m {{ $seconds }}s
                                                @endif
                                            </div>
                                            <div class="text-xs text-orange-600 mt-0.5">
                                                @if($timeLeftSeconds > 0)
                                                    @if($leftHours > 0)
                                                        {{ $leftHours }}h {{ $leftMinutes }}m {{ $leftSeconds }}s left
                                                    @else
                                                        {{ $leftMinutes }}m {{ $leftSeconds }}s left
                                                    @endif
                                                @else
                                                    <span class="text-red-600">Time exhausted</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-block bg-blue-50 px-3 py-1 rounded-full">
                                    <span class="text-blue-700 font-bold">{{ $submission->mcq_marks }}/{{ $submission->total_mcq_marks }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-block bg-green-50 px-3 py-1 rounded-full">
                                    <span class="text-green-700 font-bold text-lg">{{ $submission->total_awarded_marks }}/{{ $submission->total_marks }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($submission->pending_count > 0)
                                    <span class="status-badge status-pending">{{ $submission->pending_count }} Pending</span>
                                @else
                                    <span class="text-green-600 font-semibold">✓ None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($submission->fully_approved)
                                    <span class="status-badge status-approved">✓ Approved</span>
                                @elseif($submission->pending_count > 0)
                                    <span class="status-badge status-pending">⏳ Pending</span>
                                @else
                                    <span class="status-badge status-submitted">Submitted</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center">
                                    @if($submission->pending_count > 0)
                                        <button class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition"
                                                data-exam-id="{{ $submission->exam_id }}"
                                                data-student-id="{{ $submission->student_id }}"
                                                data-student-name="{{ addslashes($submission->student_name) }}"
                                                data-exam-name="{{ addslashes($submission->exam_name) }}"
                                                onclick="reviewAnswers(this.getAttribute('data-exam-id'), this.getAttribute('data-student-id'), this.getAttribute('data-student-name'), this.getAttribute('data-exam-name'))">
                                            Review
                                        </button>
                                    @endif
                                    <a href="{{ route('exam.view-screenshots', ['examId' => $submission->exam_id, 'studentId' => $submission->student_id]) }}" 
                                       class="px-3 py-1 bg-purple-500 text-white text-sm rounded hover:bg-purple-600 transition">
                                        Screenshots
                                    </a>
                                    <button onclick="viewDetails(this)"
                                            data-submission="{{ json_encode($submission) }}"
                                            class="px-3 py-1 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition">
                                        Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-lg font-medium">No submissions yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8 max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold mb-4" id="modalTitle">Submission Details</h2>
        <div id="modalContent" class="space-y-3"></div>
        <button onclick="closeModal()" class="mt-6 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            Close
        </button>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div id="reviewModalContent"></div>
    </div>
</div>

<script>
    function viewDetails(button) {
        const submissionData = button.getAttribute('data-submission');
        const submission = JSON.parse(submissionData);
        const modal = document.getElementById('detailsModal');
        const content = document.getElementById('modalContent');
        
        const html = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Student Name</p>
                    <p class="text-gray-900">${submission.student_name}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Email</p>
                    <p class="text-gray-900">${submission.student_email}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Exam</p>
                    <p class="text-gray-900">${submission.exam_name}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Submitted At</p>
                    <p class="text-gray-900">${new Date(submission.submitted_at).toLocaleString()}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Total Questions</p>
                    <p class="text-gray-900">${submission.total_questions}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Attempted</p>
                    <p class="text-gray-900">${submission.attempted}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">MCQ Marks</p>
                    <p class="text-blue-600 font-bold">${submission.mcq_marks}/${submission.total_mcq_marks}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Pending Descriptive</p>
                    <p class="text-yellow-600 font-bold">${submission.pending_count}</p>
                </div>
            </div>
        `;
        
        content.innerHTML = html;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    // Review answers modal
    function reviewAnswers(examId, studentId, studentName, examName) {
        const modal = document.getElementById('reviewModal');
        const content = document.getElementById('reviewModalContent');
        
        // Get pending answers for this student
        const pendingAnswers = window.pendingAnswers || {};
        const studentAnswers = pendingAnswers[examId]?.[studentId] || [];
        
        let html = `
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Review Answers</h3>
                    <button onclick="closeReviewModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800">${studentName}</h4>
                    <p class="text-blue-600">${examName}</p>
                </div>
                
                <div class="space-y-6">
        `;
        
        if (studentAnswers.length === 0) {
            html += `
                <div class="text-center py-8">
                    <p class="text-gray-500">No pending answers to review for this student.</p>
                </div>
            `;
        } else {
            studentAnswers.forEach((answer, index) => {
                html += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="mb-3">
                            <h5 class="font-semibold text-gray-800 mb-2">Question ${index + 1}:</h5>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded">${answer.question_text}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="font-semibold text-gray-800 mb-2">Student Answer:</h5>
                            <div class="text-gray-700 bg-yellow-50 p-3 rounded border-l-4 border-yellow-400">
                                ${answer.answer_text || '<em>No answer provided</em>'}
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marks (Max: ${answer.max_marks})</label>
                                    <input type="number" 
                                           id="marks_${answer.id}" 
                                           class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                           min="0" 
                                           max="${answer.max_marks}" 
                                           value="${answer.awarded_marks || 0}">
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="approveAnswer(${answer.id}, 'approve')" 
                                        class="px-4 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition">
                                    ✅ Approve
                                </button>
                                <button onclick="approveAnswer(${answer.id}, 'reject')" 
                                        class="px-4 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition">
                                    ❌ Reject
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        html += `
                </div>
            </div>
        `;
        
        content.innerHTML = html;
        modal.classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    async function approveAnswer(answerId, action) {
        const marksInput = document.getElementById(`marks_${answerId}`);
        const marks = marksInput ? marksInput.value : 0;
        
        try {
            const response = await fetch(`/approve-answer/${answerId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    action: action,
                    marks: marks
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                showToast(`${action === 'approve' ? 'Answer approved' : 'Answer rejected'} successfully!`, 'success');
                
                // Close modal and refresh page after a short delay
                setTimeout(() => {
                    closeReviewModal();
                    location.reload();
                }, 1500);
            } else {
                showToast('Error: ' + (result.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error approving answer:', error);
            showToast('Network error. Please try again.', 'error');
        }
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white font-medium z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Filter by exam
    document.getElementById('examFilter').addEventListener('change', (e) => {
        const examId = e.target.value;
        const rows = document.querySelectorAll('table tbody tr');
        
        rows.forEach(row => {
            const rowExamId = row.getAttribute('data-exam-id');
            if (!examId || rowExamId === examId) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

</body>
</html>
