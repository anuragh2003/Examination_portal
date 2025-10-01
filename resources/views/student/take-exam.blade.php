<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->name }} - Taking Exam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Fixed Header -->
    <header class="bg-white shadow-lg fixed top-0 left-0 right-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Exam Info -->
                <div class="flex items-center space-x-4">
                    <div class="text-lg font-bold text-gray-800">{{ $exam->name }}</div>
                    <div class="text-sm text-gray-600">{{ $studentSession['name'] }}</div>
                </div>

                <!-- Timer and Status -->
                <div class="flex items-center space-x-6">
                    <!-- Progress -->
                    <div class="text-sm text-gray-600">
                        Question <span id="current-question">1</span> of <span id="total-questions">{{ $questions->count() }}</span>
                    </div>

                    <!-- Timer -->
                    <div class="flex items-center space-x-2 bg-red-50 px-4 py-2 rounded-lg">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="timer" class="font-mono text-red-600 font-semibold">{{ $exam->duration_minutes }}:00</span>
                    </div>

                    <!-- Submit Button -->
                    <button onclick="submitExam()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                        📋 Submit Exam
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="pt-24 pb-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-12 gap-8">
                
                <!-- Question Panel -->
                <div class="col-span-9">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <!-- Question Navigation -->
                        <div class="mb-8">
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach($questions as $index => $question)
                                    <button onclick="goToQuestion({{ $index + 1 }})" 
                                            class="question-nav-btn w-12 h-12 rounded-lg border-2 border-gray-300 hover:border-blue-500 transition font-semibold"
                                            data-question="{{ $index + 1 }}"
                                            id="nav-btn-{{ $index + 1 }}">
                                        {{ $index + 1 }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Questions Container -->
                        <div id="questions-container">
                            @foreach($questions as $index => $question)
                                <div class="question-slide" data-question="{{ $index + 1 }}" 
                                     style="{{ $index === 0 ? '' : 'display: none;' }}">
                                     
                                    <!-- Question Header -->
                                    <div class="mb-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h2 class="text-xl font-bold text-gray-800">
                                                Question {{ $index + 1 }}
                                                <span class="text-sm font-normal text-gray-600">({{ $question->marks }} marks)</span>
                                            </h2>
                                            <div class="flex items-center space-x-2">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                    {{ $question->difficulty === 'easy' ? 'bg-green-100 text-green-800' : 
                                                       ($question->difficulty === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($question->difficulty) }}
                                                </span>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Question Text -->
                                        <div class="prose max-w-none">
                                            <p class="text-gray-700 text-lg leading-relaxed">{{ $question->text }}</p>
                                        </div>
                                    </div>

                                    <!-- Answer Section -->
                                    <div class="answer-section">
                                        @if($question->type === 'mcq_single')
                                            <!-- Single Choice MCQ -->
                                            <div class="space-y-3">
                                                @foreach($question->options as $optionIndex => $option)
                                                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                                        <input type="radio" 
                                                               name="question_{{ $question->id }}" 
                                                               value="{{ $option->id }}"
                                                               class="mr-4 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                               onchange="saveAnswer({{ $question->id }}, this.value, 'mcq_single')">
                                                        <span class="flex-1 text-gray-700">
                                                            <span class="font-medium mr-2">{{ chr(65 + $optionIndex) }})</span>
                                                            {{ $option->option_text }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>

                                        @elseif($question->type === 'mcq_multiple')
                                            <!-- Multiple Choice MCQ -->
                                            <div class="mb-4">
                                                <p class="text-sm text-gray-600 mb-3">💡 Select all correct options</p>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach($question->options as $optionIndex => $option)
                                                    <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                                        <input type="checkbox" 
                                                               name="question_{{ $question->id }}[]" 
                                                               value="{{ $option->id }}"
                                                               class="mr-4 h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                               onchange="saveMultipleAnswer({{ $question->id }})">
                                                        <span class="flex-1 text-gray-700">
                                                            <span class="font-medium mr-2">{{ chr(65 + $optionIndex) }})</span>
                                                            {{ $option->option_text }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>

                                        @else
                                            <!-- Descriptive Question -->
                                            <div>
                                                <textarea name="question_{{ $question->id }}"
                                                          placeholder="Type your answer here..."
                                                          rows="8"
                                                          class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                          onchange="saveAnswer({{ $question->id }}, this.value, 'descriptive')"></textarea>
                                                <div class="mt-2 text-sm text-gray-500">
                                                    💡 Be clear and comprehensive in your answer
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Auto-save Status -->
                                    <div class="mt-6 flex justify-between items-center">
                                        <div id="save-status-{{ $question->id }}" class="text-sm text-gray-500">
                                            <!-- Save status will be shown here -->
                                        </div>
                                        
                                        <!-- Navigation Buttons -->
                                        <div class="flex space-x-3">
                                            @if($index > 0)
                                                <button onclick="goToQuestion({{ $index }})" 
                                                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                                    ← Previous
                                                </button>
                                            @endif
                                            
                                            @if($index < $questions->count() - 1)
                                                <button onclick="goToQuestion({{ $index + 2 }})" 
                                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                    Next →
                                                </button>
                                            @else
                                                <button onclick="submitExam()" 
                                                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                                    📋 Submit Exam
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-span-3">
                    <!-- Exam Summary -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 sticky top-28">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">📊 Exam Summary</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Questions:</span>
                                <span class="font-semibold">{{ $questions->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Answered:</span>
                                <span id="answered-count" class="font-semibold text-green-600">0</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Remaining:</span>
                                <span id="remaining-count" class="font-semibold text-orange-600">{{ $questions->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Marks:</span>
                                <span class="font-semibold">{{ $exam->total_marks }}</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progress</span>
                                <span id="progress-percentage">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- Quick Navigation -->
                        <div class="mt-6">
                            <h4 class="font-semibold mb-3 text-gray-800">🎯 Quick Navigation</h4>
                            <div class="space-y-2 text-sm">
                                <button onclick="goToFirstUnanswered()" class="w-full text-left px-3 py-2 bg-orange-50 text-orange-700 rounded hover:bg-orange-100">
                                    → Next Unanswered
                                </button>
                                <button onclick="reviewAnswers()" class="w-full text-left px-3 py-2 bg-blue-50 text-blue-700 rounded hover:bg-blue-100">
                                    📝 Review Answers
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h4 class="font-semibold mb-3 text-gray-800">📚 Legend</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-blue-100 border-2 border-blue-500 rounded mr-3"></div>
                                <span class="text-gray-600">Current Question</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 border-2 border-green-500 rounded mr-3"></div>
                                <span class="text-gray-600">Answered</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-gray-100 border-2 border-gray-300 rounded mr-3"></div>
                                <span class="text-gray-600">Not Answered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div id="submit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md mx-4">
            <div class="text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-4">Submit Examination?</h3>
                <div class="text-gray-600 mb-6">
                    <p class="mb-2">You have answered <span id="final-answered-count">0</span> out of {{ $questions->count() }} questions.</p>
                    <p class="text-sm text-orange-600">Once submitted, you cannot change your answers.</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="hideSubmitModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Continue Exam
                    </button>
                    <button onclick="confirmSubmit()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Submit Final
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Exam state
        let currentQuestion = 1;
        let totalQuestions = {{ $questions->count() }};
        let answeredQuestions = new Set();
        let examDurationMinutes = {{ $exam->duration_minutes }};
        let startTime = new Date('{{ \Carbon\Carbon::parse($studentSession['start_time'])->toISOString() }}');
        let answers = {};

        // Initialize exam
        document.addEventListener('DOMContentLoaded', function() {
            startTimer();
            updateProgress();
            updateQuestionNavigation();
            
            // Prevent page refresh/navigation
            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
            });
        });

        // Timer functionality
        function startTimer() {
            setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            const now = new Date();
            const elapsed = Math.floor((now - startTime) / 1000);
            const totalSeconds = examDurationMinutes * 60;
            const remaining = Math.max(0, totalSeconds - elapsed);
            
            // Debug logging (remove in production)
            if (elapsed < 60) { // Only log for first minute
                console.log('Timer Debug:', {
                    startTime: startTime.toISOString(),
                    now: now.toISOString(),
                    elapsed: elapsed,
                    totalSeconds: totalSeconds,
                    remaining: remaining,
                    examDurationMinutes: examDurationMinutes
                });
            }
            
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // DISABLED FOR DEBUGGING: Don't auto-submit
            // if (remaining <= 0) {
            //     alert('Time is up! Submitting your exam automatically.');
            //     confirmSubmit();
            // }
            
            // Warning at 5 minutes
            if (remaining === 300) {
                alert('⚠️ 5 minutes remaining! Please review your answers.');
            }
        }

        // Question navigation
        function goToQuestion(questionNum) {
            // Hide current question
            document.querySelector(`.question-slide[data-question="${currentQuestion}"]`).style.display = 'none';
            
            // Show target question
            document.querySelector(`.question-slide[data-question="${questionNum}"]`).style.display = 'block';
            
            currentQuestion = questionNum;
            document.getElementById('current-question').textContent = currentQuestion;
            
            updateQuestionNavigation();
        }

        function updateQuestionNavigation() {
            // Update navigation buttons
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                const questionNum = parseInt(btn.dataset.question);
                
                btn.classList.remove('bg-blue-500', 'text-white', 'bg-green-500', 'border-blue-500', 'border-green-500');
                
                if (questionNum === currentQuestion) {
                    btn.classList.add('bg-blue-500', 'text-white', 'border-blue-500');
                } else if (answeredQuestions.has(questionNum)) {
                    btn.classList.add('bg-green-500', 'text-white', 'border-green-500');
                } else {
                    btn.classList.add('border-gray-300');
                }
            });
        }

        // Answer saving
        function saveAnswer(questionId, answer, type) {
            answers[questionId] = answer;
            
            // Mark question as answered
            const questionNum = getCurrentQuestionNumFromId(questionId);
            answeredQuestions.add(questionNum);
            
            // Update UI
            updateProgress();
            updateQuestionNavigation();
            showSaveStatus(questionId, 'Saving...');
            
            // AJAX save to server
            fetch(`/exam/{{ $exam->uuid }}/save-answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer: answer,
                    type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSaveStatus(questionId, `Saved at ${data.saved_at}`);
                } else {
                    showSaveStatus(questionId, 'Error saving!', 'error');
                }
            })
            .catch(error => {
                showSaveStatus(questionId, 'Network error!', 'error');
            });
        }

        function saveMultipleAnswer(questionId) {
            const checkboxes = document.querySelectorAll(`input[name=\"question_${questionId}[]\"]:checked`);
            const selectedValues = Array.from(checkboxes).map(cb => cb.value);
            
            saveAnswer(questionId, selectedValues, 'mcq_multiple');
        }

        function showSaveStatus(questionId, message, type = 'success') {
            const statusElement = document.getElementById(`save-status-${questionId}`);
            statusElement.textContent = message;
            statusElement.className = `text-sm ${type === 'error' ? 'text-red-500' : 'text-green-500'}`;
        }

        // Progress tracking
        function updateProgress() {
            const answered = answeredQuestions.size;
            const remaining = totalQuestions - answered;
            const percentage = Math.round((answered / totalQuestions) * 100);
            
            document.getElementById('answered-count').textContent = answered;
            document.getElementById('remaining-count').textContent = remaining;
            document.getElementById('progress-percentage').textContent = `${percentage}%`;
            document.getElementById('progress-bar').style.width = `${percentage}%`;
        }

        // Utility functions
        function getCurrentQuestionNumFromId(questionId) {
            const questions = @json($questions->pluck('id'));
            return questions.indexOf(parseInt(questionId)) + 1;
        }

        function goToFirstUnanswered() {
            for (let i = 1; i <= totalQuestions; i++) {
                if (!answeredQuestions.has(i)) {
                    goToQuestion(i);
                    return;
                }
            }
            alert('All questions have been answered!');
        }

        function reviewAnswers() {
            const unanswered = [];
            for (let i = 1; i <= totalQuestions; i++) {
                if (!answeredQuestions.has(i)) {
                    unanswered.push(i);
                }
            }
            
            if (unanswered.length > 0) {
                alert(`Unanswered questions: ${unanswered.join(', ')}`);
            } else {
                alert('All questions answered! Ready to submit.');
            }
        }

        // Submission
        function submitExam() {
            document.getElementById('final-answered-count').textContent = answeredQuestions.size;
            document.getElementById('submit-modal').classList.remove('hidden');
        }

        function hideSubmitModal() {
            document.getElementById('submit-modal').classList.add('hidden');
        }

        function confirmSubmit() {
            // Show loading
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/exam/{{ $exam->uuid }}/submit';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>
</html>