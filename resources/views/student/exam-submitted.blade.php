<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Submitted - {{ $exam->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen">
    
    <div class="min-h-screen flex items-center justify-center py-8">
        <div class="max-w-4xl mx-auto px-4">
            
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="mb-6">
                    <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-16 h-16 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                <h1 class="text-4xl font-bold text-gray-800 mb-2">🎉 Exam Submitted Successfully!</h1>
                <p class="text-xl text-gray-600">Thank you for completing <strong>{{ $exam->name }}</strong></p>
            </div>

            <!-- Results Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                
                <!-- Student Information -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Student Information
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Full Name</label>
                            <p class="text-lg font-semibold text-gray-800">{{ $submission['student']['name'] }}</p>
                        </div>
                        
                        <div class="border-l-4 border-blue-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Email Address</label>
                            <p class="text-lg font-semibold text-gray-800">{{ $submission['student']['email'] }}</p>
                        </div>
                        
                        @if($submission['student']['student_id'])
                            <div class="border-l-4 border-blue-500 pl-4">
                                <label class="text-sm font-medium text-gray-600">Student ID</label>
                                <p class="text-lg font-semibold text-gray-800">{{ $submission['student']['student_id'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Exam Information -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exam Details
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="border-l-4 border-purple-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Exam Name</label>
                            <p class="text-lg font-semibold text-gray-800">{{ $submission['exam']['name'] }}</p>
                        </div>
                        
                        <div class="border-l-4 border-purple-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Started At</label>
                            <p class="text-lg font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($submission['submission']['start_time'])->format('M j, Y \a\t g:i A') }}
                            </p>
                        </div>
                        
                        <div class="border-l-4 border-purple-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Submitted At</label>
                            <p class="text-lg font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($submission['submission']['submit_time'])->format('M j, Y \a\t g:i A') }}
                            </p>
                        </div>
                        
                        <div class="border-l-4 border-purple-500 pl-4">
                            <label class="text-sm font-medium text-gray-600">Time Taken</label>
                            <p class="text-lg font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($submission['submission']['start_time'])->diffInMinutes(\Carbon\Carbon::parse($submission['submission']['submit_time'])) }} minutes
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Summary -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Performance Summary
                </h2>

                <!-- Score Display -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl font-bold text-blue-600">{{ $submission['submission']['results']['percentage'] }}%</span>
                        </div>
                        <p class="text-sm font-medium text-gray-600">Overall Score</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-green-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl font-bold text-green-600">{{ $submission['submission']['results']['correct'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-600">Correct Answers</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-red-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl font-bold text-red-600">{{ $submission['submission']['results']['incorrect'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-600">Incorrect</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl font-bold text-orange-600">{{ $submission['submission']['results']['unanswered'] }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-600">Unanswered</p>
                    </div>
                </div>

                <!-- Detailed Results -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-xl">
                        <h4 class="font-semibold text-blue-800 mb-2">📝 Questions</h4>
                        <p class="text-2xl font-bold text-blue-900">{{ $submission['submission']['results']['total_questions'] }}</p>
                        <p class="text-sm text-blue-700">Total Questions</p>
                    </div>
                    
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-xl">
                        <h4 class="font-semibold text-purple-800 mb-2">🎯 Score</h4>
                        <p class="text-2xl font-bold text-purple-900">{{ $submission['submission']['results']['earned_marks'] }}/{{ $submission['submission']['results']['total_marks'] }}</p>
                        <p class="text-sm text-purple-700">Marks Obtained</p>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-xl">
                        <h4 class="font-semibold text-green-800 mb-2">⏱️ Completion</h4>
                        <p class="text-2xl font-bold text-green-900">{{ round(($submission['submission']['results']['answered'] / $submission['submission']['results']['total_questions']) * 100) }}%</p>
                        <p class="text-sm text-green-700">Questions Attempted</p>
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-2xl mb-8">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-600 mr-3 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">📋 Important Information</h3>
                        <ul class="text-yellow-700 space-y-1 text-sm">
                            <li>• Your exam has been successfully submitted and recorded</li>
                            <li>• Results shown are preliminary and may be subject to review</li>
                            <li>• Descriptive answers will be evaluated manually</li>
                            <li>• Final results will be communicated through official channels</li>
                            <li>• Keep this confirmation for your records</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center space-y-4">
                <div class="space-x-4">
                    <button onclick="window.print()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-semibold transition">
                        🖨️ Print Results
                    </button>
                    
                    <button onclick="downloadResults()" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 font-semibold transition">
                        📄 Download Summary
                    </button>
                </div>
                
                <p class="text-gray-600 text-sm">
                    Thank you for using our examination portal. Good luck! 🎓
                </p>
            </div>
        </div>
    </div>

    <script>
        // Download results as text file
        function downloadResults() {
            const results = `
EXAMINATION RESULTS SUMMARY
===========================

Student Information:
- Name: {{ $submission['student']['name'] }}
- Email: {{ $submission['student']['email'] }}
@if($submission['student']['student_id'])
- Student ID: {{ $submission['student']['student_id'] }}
@endif

Exam Information:
- Exam: {{ $submission['exam']['name'] }}
- Started: {{ \Carbon\Carbon::parse($submission['submission']['start_time'])->format('M j, Y \a\t g:i A') }}
- Submitted: {{ \Carbon\Carbon::parse($submission['submission']['submit_time'])->format('M j, Y \a\t g:i A') }}
- Duration: {{ \Carbon\Carbon::parse($submission['submission']['start_time'])->diffInMinutes(\Carbon\Carbon::parse($submission['submission']['submit_time'])) }} minutes

Performance Summary:
- Total Questions: {{ $submission['submission']['results']['total_questions'] }}
- Correct Answers: {{ $submission['submission']['results']['correct'] }}
- Incorrect Answers: {{ $submission['submission']['results']['incorrect'] }}
- Unanswered: {{ $submission['submission']['results']['unanswered'] }}
- Score: {{ $submission['submission']['results']['earned_marks'] }}/{{ $submission['submission']['results']['total_marks'] }} ({{ $submission['submission']['results']['percentage'] }}%)

Generated on: ${new Date().toLocaleString()}
            `.trim();

            const blob = new Blob([results], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'exam_results_{{ $submission["exam"]["name"] }}_{{ \Carbon\Carbon::now()->format("Y-m-d") }}.txt';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Prevent going back to exam
        history.pushState(null, document.title, location.href);
        window.addEventListener('popstate', function (event) {
            alert('You have completed the exam. You cannot go back to the exam page.');
            history.pushState(null, document.title, location.href);
        });

        // Print styles
        const printStyles = `
            <style media="print">
                body { 
                    background: white !important; 
                    font-size: 12px !important;
                }
                .bg-gradient-to-br { background: white !important; }
                .shadow-lg { box-shadow: none !important; }
                button { display: none !important; }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', printStyles);
    </script>

</body>
</html>