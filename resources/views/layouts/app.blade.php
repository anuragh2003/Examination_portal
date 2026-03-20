<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Examination Portal')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }
        
        .badge {
            border-radius: 0.25rem;
        }
        
        .main-content {
            min-height: calc(100vh - 120px);
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        footer {
            background-color: #343a40;
            color: #fff;
            padding: 1rem 0;
            margin-top: auto;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap"></i> Examination Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('exams.create') }}">
                            <i class="fas fa-plus"></i> Create Exam
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @if(session('user'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> Welcome, {{ session('user.name', 'Admin') }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© {{ date('Y') }} Examination Portal. All rights reserved.</p>
            <small class="text-muted">Built with Laravel & TailwindCSS</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Confirm Modal -->
    <div class="modal fade" id="customConfirmModal" tabindex="-1" aria-labelledby="customConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="customConfirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Are you sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmYesBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Prompt Modal -->
    <div class="modal fade" id="customPromptModal" tabindex="-1" aria-labelledby="customPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="customPromptModalLabel">Input Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="promptModalMessage">Enter value:</p>
                    <input type="text" class="form-control" id="promptInput" placeholder="Enter value">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="promptOkBtn">OK</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Custom alert functions to replace browser alerts
        function showCustomAlert(message, type = 'info', title = 'Message') {
            const modal = new bootstrap.Modal(document.getElementById('customAlertModal'));
            const modalHeader = document.getElementById('modalHeader');
            const modalBody = document.getElementById('modalBody');
            const modalTitle = document.getElementById('customAlertModalLabel');
            
            // Set title
            modalTitle.textContent = title;
            
            // Set message
            modalBody.textContent = message;
            
            // Set header color based on type
            modalHeader.className = 'modal-header';
            if (type === 'success') {
                modalHeader.classList.add('bg-success', 'text-white');
            } else if (type === 'error' || type === 'danger') {
                modalHeader.classList.add('bg-danger', 'text-white');
            } else if (type === 'warning') {
                modalHeader.classList.add('bg-warning');
            } else {
                modalHeader.classList.add('bg-primary', 'text-white');
            }
            
            modal.show();
        }

        // Convenience functions
        function showSuccess(message, title = 'Success') {
            showCustomAlert(message, 'success', title);
        }

        function showError(message, title = 'Error') {
            showCustomAlert(message, 'error', title);
        }

        function showWarning(message, title = 'Warning') {
            showCustomAlert(message, 'warning', title);
        }

        function showInfo(message, title = 'Information') {
            showCustomAlert(message, 'info', title);
        }

        // Custom confirm function to replace browser confirm
        function showCustomConfirm(message, title = 'Confirm Action') {
            return new Promise((resolve) => {
                const modal = new bootstrap.Modal(document.getElementById('customConfirmModal'));
                const modalBody = document.getElementById('confirmModalBody');
                const modalTitle = document.getElementById('customConfirmModalLabel');
                
                modalTitle.textContent = title;
                modalBody.textContent = message;
                
                const yesBtn = document.getElementById('confirmYesBtn');
                const noBtn = modal._element.querySelector('.btn-secondary');
                
                const handleYes = () => {
                    modal.hide();
                    resolve(true);
                    yesBtn.removeEventListener('click', handleYes);
                    noBtn.removeEventListener('click', handleNo);
                };
                
                const handleNo = () => {
                    modal.hide();
                    resolve(false);
                    yesBtn.removeEventListener('click', handleYes);
                    noBtn.removeEventListener('click', handleNo);
                };
                
                yesBtn.addEventListener('click', handleYes);
                noBtn.addEventListener('click', handleNo);
                
                modal.show();
            });
        }

        // Custom prompt function to replace browser prompt
        function showCustomPrompt(message, defaultValue = '', title = 'Input Required') {
            return new Promise((resolve) => {
                const modal = new bootstrap.Modal(document.getElementById('customPromptModal'));
                const modalTitle = document.getElementById('customPromptModalLabel');
                const modalMessage = document.getElementById('promptModalMessage');
                const input = document.getElementById('promptInput');
                
                modalTitle.textContent = title;
                modalMessage.textContent = message;
                input.value = defaultValue;
                
                const okBtn = document.getElementById('promptOkBtn');
                const cancelBtn = modal._element.querySelector('.btn-secondary');
                
                const handleOk = () => {
                    modal.hide();
                    resolve(input.value);
                    okBtn.removeEventListener('click', handleOk);
                    cancelBtn.removeEventListener('click', handleCancel);
                };
                
                const handleCancel = () => {
                    modal.hide();
                    resolve(null);
                    okBtn.removeEventListener('click', handleOk);
                    cancelBtn.removeEventListener('click', handleCancel);
                };
                
                okBtn.addEventListener('click', handleOk);
                cancelBtn.addEventListener('click', handleCancel);
                
                modal.show();
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>