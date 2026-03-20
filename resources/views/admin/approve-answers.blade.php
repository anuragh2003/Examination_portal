@extends('layouts.app')

@section('title', 'Approve Descriptive Answers')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-check-circle"></i> Approve Descriptive Answers</h4>
                </div>
                <div class="card-body">
                    @if($pendingAnswers->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No Pending Answers</h5>
                            <p class="text-muted">All descriptive answers have been reviewed.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($pendingAnswers as $answer)
                                <div class="col-md-6 mb-4">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-white">
                                            <strong>{{ $answer->exam_name }}</strong> - {{ $answer->candidate_name }}
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title">Question:</h6>
                                            <p class="card-text">{{ $answer->question_text }}</p>
                                            <h6 class="card-title">Student Answer:</h6>
                                            <p class="card-text bg-light p-2 rounded">{{ $answer->answer_text ?: 'No answer provided' }}</p>
                                            <p class="text-muted">Max Marks: {{ $answer->max_marks }}</p>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-success btn-sm approve-btn" data-answer-id="{{ $answer->id ?? 0 }}" data-max-marks="{{ $answer->max_marks ?? 0 }}">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button class="btn btn-danger btn-sm reject-btn" data-answer-id="{{ $answer->id ?? 0 }}">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Approve buttons
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const answerId = this.dataset.answerId;
            const maxMarks = this.dataset.maxMarks;
            approveAnswer(answerId, maxMarks);
        });
    });

    // Reject buttons
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const answerId = this.dataset.answerId;
            rejectAnswer(answerId);
        });
    });
});

async function approveAnswer(answerId, maxMarks) {
    const marks = await showCustomPrompt(`Enter marks (0-${maxMarks}):`, maxMarks, 'Approve Answer');
    if (marks !== null && marks !== '') {
        fetch(`/approve-answer/${answerId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                action: 'approve',
                marks: parseInt(marks)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Answer approved!');
                location.reload();
            } else {
                showError('Error: ' + data.message, 'Approval Failed');
            }
        });
    }
}

function rejectAnswer(answerId) {
    showCustomConfirm('Are you sure you want to reject this answer?', 'Reject Answer')
        .then(confirmed => {
            if (confirmed) {
                fetch(`/approve-answer/${answerId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: 'reject'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess('Answer rejected!');
                        location.reload();
                    } else {
                        showError('Error: ' + data.message, 'Rejection Failed');
                    }
                });
            }
        });
}
</script>
@endsection