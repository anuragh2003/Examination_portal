@extends('layouts.app')

@section('title', 'Exam Preview - ' . $exam->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Exam Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3><i class="fas fa-eye"></i> Exam Preview</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('exams.show', $exam->uuid) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Management
                            </a>
                            <button onclick="window.print()" class="btn btn-light btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h2 class="text-primary">{{ $exam->name }}</h2>
                            <p class="text-muted mb-0">This is how students will see this exam</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="exam-meta">
                                <div><strong>Total Marks:</strong> {{ $exam->total_marks }}</div>
                                <div><strong>Duration:</strong> {{ $exam->duration_minutes }} minutes</div>
                                <div><strong>Questions:</strong> {{ count($questions) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Instructions (Student View) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Exam Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><strong>Important Instructions:</strong></h6>
                        <ul class="mb-0">
                            <li>This exam contains <strong>{{ count($questions) }} questions</strong> with a total of <strong>{{ $exam->total_marks }} marks</strong></li>
                            <li>You have <strong>{{ $exam->duration_minutes }} minutes</strong> to complete this exam</li>
                            <li>Read each question carefully before answering</li>
                            <li>For multiple choice questions, select the best answer(s)</li>
                            <li>All questions are compulsory</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            @if(count($questions) > 0)
                @foreach($questions as $index => $question)
                    <div class="card mb-4 question-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <span class="badge bg-secondary me-2">Q{{ $index + 1 }}</span>
                                <span class="badge bg-primary">{{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}</span>
                                <span class="badge bg-info ms-2">{{ ucfirst($question->difficulty) }}</span>
                            </h6>
                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $question->type)) }}</small>
                        </div>
                        <div class="card-body">
                            <div class="question-text mb-3">
                                <p class="h6">{{ $question->text }}</p>
                            </div>

                            <!-- MCQ Options -->
                            @if(in_array($question->type, ['mcq_single', 'mcq_multiple']) && isset($question->options))
                                <div class="options">
                                    @if($question->type === 'mcq_single')
                                        <p class="text-muted small mb-2"><em>Select the best answer:</em></p>
                                        @foreach($question->options as $optIndex => $option)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" 
                                                       name="question_{{ $question->id }}" 
                                                       id="q{{ $question->id }}_opt{{ $optIndex }}"
                                                       disabled>
                                                <label class="form-check-label" for="q{{ $question->id }}_opt{{ $optIndex }}">
                                                    <strong>{{ chr(65 + $optIndex) }})</strong> {{ $option->option_text }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted small mb-2"><em>Select all correct answers:</em></p>
                                        @foreach($question->options as $optIndex => $option)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="question_{{ $question->id }}[]" 
                                                       id="q{{ $question->id }}_opt{{ $optIndex }}"
                                                       disabled>
                                                <label class="form-check-label" for="q{{ $question->id }}_opt{{ $optIndex }}">
                                                    <strong>{{ chr(65 + $optIndex) }})</strong> {{ $option->option_text }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            <!-- Descriptive Question -->
                            @if($question->type === 'descriptive')
                                <div class="descriptive-answer">
                                    <p class="text-muted small mb-2"><em>Write your answer below:</em></p>
                                    <textarea class="form-control" rows="6" 
                                              placeholder="Type your answer here..." 
                                              disabled></textarea>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Exam Summary -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Exam Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h3 class="text-primary">{{ count($questions) }}</h3>
                                    <p class="text-muted">Total Questions</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h3 class="text-success">{{ $exam->total_marks }}</h3>
                                    <p class="text-muted">Total Marks</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h3 class="text-info">{{ $exam->duration_minutes }}</h3>
                                    <p class="text-muted">Minutes</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-box">
                                    <h3 class="text-warning">{{ number_format($exam->total_marks / count($questions), 1) }}</h3>
                                    <p class="text-muted">Avg Marks/Q</p>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button type="button" class="btn btn-success btn-lg me-md-2" disabled>
                                <i class="fas fa-paper-plane"></i> Submit Exam (Preview Mode)
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Exam
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Questions Added</h4>
                        <p class="text-muted">This exam doesn't have any questions yet. Use the "Regenerate Questions" button to add questions automatically.</p>
                        <a href="{{ route('exams.show', $exam->uuid) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Questions
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .question-card {
        border-left: 4px solid #007bff;
        transition: box-shadow 0.2s;
    }
    
    .question-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .exam-meta div {
        padding: 2px 0;
    }
    
    .stat-box {
        padding: 1rem;
    }
    
    .stat-box h3 {
        margin-bottom: 0.5rem;
    }
    
    .options .form-check-label {
        cursor: default;
    }
    
    @media print {
        .btn, .card-header .d-flex .btn {
            display: none !important;
        }
        
        .container {
            max-width: none !important;
        }
        
        .question-card {
            break-inside: avoid;
            margin-bottom: 20px;
        }
        
        body {
            font-size: 12px;
        }
    }
</style>
@endpush