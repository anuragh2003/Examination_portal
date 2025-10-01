@extends('layouts.app')

@section('title', 'Exam Details - ' . $exam->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Exam Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3><i class="fas fa-clipboard-list"></i> {{ $exam->name }}</h3>
                        <small class="text-muted">Created: {{ $exam->created_at->format('M d, Y') }}</small>
                    </div>
                    <div>
                        <span class="badge badge-{{ $exam->status == 'active' ? 'success' : ($exam->status == 'draft' ? 'warning' : 'secondary') }} badge-lg">
                            {{ ucfirst($exam->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total Marks:</strong><br>
                            <span class="h4 text-primary">{{ $exam->total_marks }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Duration:</strong><br>
                            <span class="h4 text-info">{{ $exam->duration_minutes }} min</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Exam ID:</strong><br>
                            <code>{{ $exam->uuid }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>Questions:</strong><br>
                            <span class="h4 text-success" id="question-count">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-tools"></i> Exam Management</h5>
                </div>
                <div class="card-body">
                    <div class="btn-toolbar" role="toolbar">
                        <div class="btn-group me-2" role="group">
                            <button type="button" class="btn btn-success" id="regenerate-questions">
                                <i class="fas fa-refresh"></i> Regenerate Questions
                            </button>
                            <button type="button" class="btn btn-primary" id="preview-exam">
                                <i class="fas fa-eye"></i> Preview Exam
                            </button>
                        </div>
                        <div class="btn-group me-2" role="group">
                            <button type="button" class="btn btn-info" id="export-exam">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-warning" id="copy-link">
                                <i class="fas fa-copy"></i> Copy Link
                            </button>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="{{ route('csv.import.form') }}" class="btn btn-secondary">
                                <i class="fas fa-upload"></i> Import Questions
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-question-circle"></i> Exam Questions</h5>
                </div>
                <div class="card-body">
                    <div id="questions-container">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">Loading questions...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="question-details">
                    <!-- Question details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="remove-question">
                    <i class="fas fa-trash"></i> Remove Question
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const examUuid = '{{ $exam->uuid }}';
    
    // Load exam questions on page load
    loadExamQuestions();
    
    // Event listeners
    document.getElementById('regenerate-questions').addEventListener('click', regenerateQuestions);
    document.getElementById('preview-exam').addEventListener('click', previewExam);
    document.getElementById('export-exam').addEventListener('click', exportExam);
    document.getElementById('copy-link').addEventListener('click', copyExamLink);
    
    /**
     * Load current exam questions via AJAX
     */
    function loadExamQuestions() {
        showLoading();
        
        fetch(`/exams/${examUuid}/questions`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQuestions(data.questions);
                    document.getElementById('question-count').textContent = data.question_count;
                } else {
                    showError('Failed to load questions: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error loading questions:', error);
                showError('Error loading questions. Please try again.');
            });
    }
    
    /**
     * Regenerate questions using QuestionSelector
     */
    function regenerateQuestions() {
        if (!confirm('This will replace all current questions with newly selected ones. Continue?')) {
            return;
        }
        
        showLoading('Generating questions...');
        
        fetch(`/exams/${examUuid}/regenerate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(`Questions regenerated successfully! Selected ${data.data.question_count} questions totaling ${data.data.total_marks} marks using ${data.data.algorithm_used} algorithm.`);
                loadExamQuestions(); // Reload questions
            } else {
                showError('Failed to regenerate questions: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error regenerating questions:', error);
            showError('Error regenerating questions. Please try again.');
        });
    }
    
    /**
     * Preview exam as students will see it
     */
    function previewExam() {
        window.open(`/exams/${examUuid}/preview`, '_blank');
    }
    
    /**
     * Export exam to downloadable format
     */
    function exportExam() {
        window.location.href = `/exams/${examUuid}/export`;
    }
    
    /**
     * Copy exam link for candidates
     */
    function copyExamLink() {
        const examUrl = `${window.location.origin}/exam/${examUuid}`;
        navigator.clipboard.writeText(examUrl).then(() => {
            showSuccess('Exam link copied to clipboard!');
        }).catch(() => {
            // Fallback for browsers that don't support clipboard API
            prompt('Copy this exam link:', examUrl);
        });
    }
    
    /**
     * Display questions in the UI
     */
    function displayQuestions(questions) {
        const container = document.getElementById('questions-container');
        
        if (questions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Questions Added</h5>
                    <p class="text-muted">Click "Regenerate Questions" to automatically select questions for this exam.</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        let totalMarks = 0;
        
        questions.forEach((question, index) => {
            totalMarks += parseInt(question.marks);
            
            const questionText = question.text.length > 100 ? 
                question.text.substring(0, 100) + '...' : question.text;
            
            html += `
                <div class="question-item" data-question-id="${question.id}">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <span class="badge bg-primary">${index + 1}</span>
                        </div>
                        <div class="col-md-7">
                            <strong>${questionText}</strong>
                            <br>
                            <small class="text-muted">
                                Type: ${question.type.replace('_', ' ')} | 
                                Difficulty: ${question.difficulty}
                            </small>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge bg-success">${question.marks} marks</span>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="removeQuestion(${question.id})">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update total marks display if needed
        const totalElement = document.querySelector('.total-marks-display');
        if (totalElement) {
            totalElement.textContent = totalMarks;
        }
    }
    
    /**
     * Remove a specific question from exam
     */
    window.removeQuestion = function(questionId) {
        if (!confirm('Remove this question from the exam?')) {
            return;
        }
        
        fetch(`/exams/${examUuid}/detach-question/${questionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Question removed successfully!');
                loadExamQuestions(); // Reload questions
            } else {
                showError('Failed to remove question: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error removing question:', error);
            showError('Error removing question. Please try again.');
        });
    };
    
    /**
     * Show loading state
     */
    function showLoading(message = 'Loading...') {
        document.getElementById('questions-container').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">${message}</p>
            </div>
        `;
    }
    
    /**
     * Show success message
     */
    function showSuccess(message) {
        showAlert(message, 'success');
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        showAlert(message, 'danger');
    }
    
    /**
     * Show alert message
     */
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.container');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>

<style>
.badge-lg {
    font-size: 0.9em;
    padding: 0.5em 0.8em;
}

.btn-toolbar .btn-group:not(:last-child) {
    margin-right: 0.5rem;
}

#questions-container {
    min-height: 200px;
}

.question-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.2s;
}

.question-item:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    cursor: pointer;
}
</style>
@endsection