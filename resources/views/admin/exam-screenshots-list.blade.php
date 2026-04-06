@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding: 20px;">
    <!-- Header -->
    <div style="margin-bottom: 30px;">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="margin-bottom: 15px;">
            ← Back to Dashboard
        </a>
        <h1 style="margin: 0; font-size: 28px; color: #333;">Proctor Recording - {{ $exam->name }}</h1>
        <p style="color: #666; margin-top: 5px;">Screenshot Gallery for All Students</p>
    </div>

    <!-- Info Card -->
    <div class="card" style="margin-bottom: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
        <div class="card-body" style="color: white;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 12px; font-weight: 600; text-transform: uppercase;">Exam</p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700;">{{ $exam->name }}</p>
                </div>
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 12px; font-weight: 600; text-transform: uppercase;">Duration</p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700;">{{ $exam->duration_minutes }} min</p>
                </div>
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 12px; font-weight: 600; text-transform: uppercase;">Total Students</p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700;">{{ count($students) }}</p>
                </div>
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 12px; font-weight: 600; text-transform: uppercase;">Total Frames</p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700;">
                        {{ array_sum(array_column($students, 'screenshot_count')) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Students List -->
    @if(count($students) > 0)
        <div class="card">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                <h5 style="margin: 0; color: #333;">Students' Screenshot Sessions</h5>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">Click on a student to view their screenshot gallery</p>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table table-hover" style="margin: 0;">
                        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <tr>
                                <th style="padding: 15px; color: #333; font-weight: 600; border-left: 3px solid transparent;">Student Name</th>
                                <th style="padding: 15px; color: #333; font-weight: 600;">Email</th>
                                <th style="padding: 15px; color: #333; font-weight: 600; text-align: center;">Screenshots</th>
                                <th style="padding: 15px; color: #333; font-weight: 600; text-align: center;">Status</th>
                                <th style="padding: 15px; color: #333; font-weight: 600; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 15px; vertical-align: middle;">
                                    <div style="font-weight: 600; color: #333;">{{ $student['name'] }}</div>
                                </td>
                                <td style="padding: 15px; vertical-align: middle; color: #666;">
                                    {{ $student['email'] }}
                                </td>
                                <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                    <span style="display: inline-block; padding: 6px 12px; background: #e7f3ff; color: #0066cc; border-radius: 4px; font-weight: 600; font-size: 13px;">
                                        📸 {{ $student['screenshot_count'] }}
                                    </span>
                                </td>
                                <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                    @if($student['screenshot_count'] > 0)
                                        <span style="display: inline-block; padding: 4px 8px; background: #d4edda; color: #155724; border-radius: 3px; font-size: 12px; font-weight: 600;">
                                            ✅ Captured
                                        </span>
                                    @else
                                        <span style="display: inline-block; padding: 4px 8px; background: #f8d7da; color: #721c24; border-radius: 3px; font-size: 12px; font-weight: 600;">
                                            ⚠️ No Data
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 15px; vertical-align: middle; text-align: center;">
                                    @if($student['screenshot_count'] > 0)
                                        <a href="{{ $student['view_url'] }}" 
                                           class="btn btn-sm" 
                                           style="background: #0066cc; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: 600; transition: background 0.2s;">
                                            View Gallery
                                        </a>
                                    @else
                                        <button class="btn btn-sm" disabled style="background: #ccc; color: #999; padding: 6px 12px; border: none; cursor: not-allowed; border-radius: 4px;">
                                            No Data
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 40px;">
                <p style="font-size: 18px; color: #999; margin: 0;">
                    📋 No students have taken this exam yet
                </p>
                <p style="color: #ccc; margin-top: 10px;">Screenshot data will appear here once students complete the exam.</p>
                
                <!-- Debug Info -->
                <div style="margin-top: 25px; padding: 15px; background: #f0f7ff; border-left: 4px solid #0066cc; text-align: left; border-radius: 4px;">
                    <h6 style="margin: 0 0 12px 0; font-weight: 700; color: #0066cc;">ℹ️ How Screenshots Appear</h6>
                    <ol style="margin: 0; padding-left: 20px; line-height: 1.8; color: #333; font-size: 13px;">
                        <li><strong>Create Exam</strong> ✓ (you did this)</li>
                        <li><strong>Student Takes Exam</strong> - Share exam link with them</li>
                        <li><strong>Screenshots Captured</strong> - Every 1.5 seconds automatically</li>
                        <li><strong>Student Submits</strong> - All screenshots upload when exam ends</li>
                        <li><strong>View Here</strong> - They appear in this list!</li>
                    </ol>
                    <p style="margin: 12px 0 0 0; color: #0066cc; font-size: 12px;">
                        💡 <strong>Next Step:</strong> Get students to take this exam. Their screenshots will show up automatically!
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table-hover tbody tr:hover {
        background: #f8f9fa;
    }

    .btn-sm:hover:not(:disabled) {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>
@endsection
