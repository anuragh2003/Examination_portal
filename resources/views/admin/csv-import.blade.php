@extends('layouts.app')

@section('title', 'CSV Import - Question Bank')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-upload"></i> CSV Import - Question Bank</h4>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> {{ session('success') }}</h5>
                            
                            @if(session('import_stats'))
                                @php $stats = session('import_stats') @endphp
                                <div class="mt-3">
                                    <h6>Import Statistics:</h6>
                                    <ul class="mb-0">
                                        <li><strong>Total Rows:</strong> {{ $stats['total_rows'] }}</li>
                                        <li><strong>Imported:</strong> {{ $stats['imported'] }}</li>
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

                    <form action="{{ route('csv.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <h5>📋 CSV Format Requirements</h5>
                            <div class="alert alert-info">
                                <p><strong>Required Columns:</strong></p>
                                <ul class="mb-2">
                                    <li><code>question_text</code> - The question text</li>
                                    <li><code>type</code> - Question type: mcq_single, mcq_multiple, descriptive</li>
                                    <li><code>marks</code> - Question marks (integer)</li>
                                </ul>
                                
                                <p><strong>Optional Columns:</strong></p>
                                <ul class="mb-2">
                                    <li><code>difficulty</code> - easy, medium, hard</li>
                                    <li><code>tags</code> - Comma-separated tags</li>
                                    <li><code>status</code> - active, inactive</li>
                                </ul>
                                
                                <p><strong>MCQ Options (for mcq_single/mcq_multiple):</strong></p>
                                <ul class="mb-0">
                                    <li><code>option_1</code>, <code>option_2</code>, ... <code>option_6</code></li>
                                    <li><code>correct_1</code>, <code>correct_2</code>, ... <code>correct_6</code> (true/false)</li>
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
                                <i class="fas fa-upload"></i> Import Questions
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5>📄 Sample CSV Structure</h5>
                        <div class="bg-light p-3 rounded">
                            <code>
question_text,type,marks,difficulty,tags,status,option_1,correct_1,option_2,correct_2,option_3,correct_3,option_4,correct_4<br>
"What is PHP?",mcq_single,5,easy,"php,backend",active,"A programming language",true,"A database",false,"An OS",false,"A framework",false<br>
"Select all PHP frameworks",mcq_multiple,10,medium,"php,frameworks",active,"Laravel",true,"CodeIgniter",true,"MySQL",false,"Apache",false
                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection