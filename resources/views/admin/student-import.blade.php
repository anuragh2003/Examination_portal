@extends('layouts.app')

@section('title', 'Import Students')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users"></i> Import Students</h4>
                    <a href="{{ route('students.list') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> {{ session('success') }}</h5>
                            @if(session('import_stats'))
                                @php $stats = session('import_stats') @endphp
                                <div class="mt-3">
                                    <h6>Import Summary</h6>
                                    <ul class="mb-0">
                                        <li><strong>Total Rows:</strong> {{ $stats['total_rows'] }}</li>
                                        <li><strong>Created / Updated:</strong> {{ $stats['imported'] }}</li>
                                        <li><strong>Skipped:</strong> {{ $stats['skipped'] }}</li>
                                        @if(!empty($stats['errors']))
                                            <li><strong>Errors:</strong>
                                                <ul>
                                                    @foreach($stats['errors'] as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Upload Errors</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <h5>📋 CSV Format Requirements</h5>
                            <div class="alert alert-info">
                                <p><strong>Required Columns:</strong></p>
                                <ul class="mb-2">
                                    <li><code>name</code> - Candidate full name</li>
                                    <li><code>email</code> - candidate email address</li>
                                    <li><code>mobile</code> - candidate mobile number</li>
                                    <li><code>role</code> - student role for exam authorization</li>
                                </ul>
                                <p><strong>Optional Columns:</strong></p>
                                <ul>
                                    <li><code>city</code> - Candidate city</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">
                                <i class="fas fa-file-csv"></i> Select CSV File
                            </label>
                            <input type="file"
                                   class="form-control @error('csv_file') is-invalid @enderror"
                                   id="csv_file"
                                   name="csv_file"
                                   accept=".csv,.txt"
                                   required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Students
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5>📄 Sample CSV Structure</h5>
                        <div class="bg-light p-3 rounded">
                            <code>
name,email,mobile,role,city<br>
"John Doe",john.doe@example.com,9876543210,Developer,Delhi<br>
"Jane Smith",jane.smith@example.com,9123456780,QA,Mumbai
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
