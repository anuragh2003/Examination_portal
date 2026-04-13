<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>{{ $exam->name }} - Take Exam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800" style="display: none;">

<script>
    // ===== INITIALIZATION CHECK FOR ALREADY SUBMITTED EXAMS =====
    let submissionCheckProceed = true;
    const submissionCheckPromise = (async function() {
        console.log("🔍 Starting async submission check");
        try {
            const response = await fetch('/exam/{{ $exam->uuid }}/check-submission', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            console.log("🔍 Async fetch status:", response.status);
            if (response.ok) {
                const data = await response.json();
                console.log("🔍 Async check response:", data);
                if (data.submitted) {
                    console.log("🚫 Exam already submitted, redirecting immediately");
                    submissionCheckProceed = false;
                    window.location.replace('/exam/{{ $exam->uuid }}/submitted');
                    return;
                }
                console.log("✅ Exam not submitted, proceeding");
                return;
            }

            console.error("❌ Async check failed with status:", response.status);
        } catch (e) {
            console.error("❌ Error in async submission check:", e);
        } finally {
            if (submissionCheckProceed) {
                console.log("🔍 Showing page after submission check");
                document.body.style.display = 'block';
            }
        }
    })();
</script>

<!-- Custom Alert Modal -->
<div class="modal fade" id="customAlertModal" tabindex="-1" aria-labelledby="customAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="customAlertModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                Message content here
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirm Modal -->
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-labelledby="customConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
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

<!-- Permission Overlay to prevent accidental clicks during permission requests -->
<div id="permissionOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display: block; z-index: 9999;">
    <div class="bg-white p-6 rounded-lg shadow-xl text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-lg font-semibold text-gray-800">Requesting Permissions</p>
        <p class="text-gray-600">Please allow camera and screen sharing access</p>
    </div>
</div>

<!-- Exam Ready Confirmation Modal -->
<div class="modal fade" id="examReadyModal" tabindex="-1" aria-labelledby="examReadyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-green-600 text-white">
                <h5 class="modal-title" id="examReadyModalLabel">Ready to Start Exam</h5>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-lg font-semibold mb-2">Permissions Granted Successfully!</p>
                <p class="text-gray-600 mb-4">Please ensure any floating screen share indicators are dismissed before starting.</p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-yellow-800">
                        <strong>Important:</strong> If you see a floating screen recording indicator, click it to hide/dismiss it before proceeding.
                    </p>
                </div>
                <p class="text-gray-700">Click "Start Exam" when you're ready.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success px-6 py-2" id="startExamBtn">
                    <svg class="inline w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Start Exam
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto mt-4 md:mt-10 bg-white p-4 md:p-8 shadow-2xl rounded-xl border border-gray-100" id="examContent" style="display: none;">

    <h1 class="text-xl md:text-3xl font-extrabold text-gray-900 mb-2 border-b pb-2">{{ $exam->name }}</h1>
    <p class="mb-4 text-gray-500 italic">{{ $exam->description }}</p>
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <p class="font-bold text-lg text-blue-700">Duration: <span class="text-blue-900">{{ $exam->duration_minutes }} minutes</span></p>
        <p id="timer" class="text-xl md:text-2xl text-red-700 font-extrabold tracking-wide bg-red-100 px-3 py-1 rounded-full shadow-inner"></p>
    </div>

    <form id="examForm" method="POST" action="/exam/{{ $exam->uuid }}/submit">
        @csrf

        @if(count($questions ?? []) === 0)
            <div style="background: red; color: white; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <h2 style="color: white;">❌ No Questions Found</h2>
                <p>This exam has no questions configured. Please contact your administrator.</p>
                <p>Exam ID: {{ $exam->id ?? 'N/A' }}</p>
                <p>Exam UUID: {{ $exam->uuid ?? 'N/A' }}</p>
            </div>
        @else
            @foreach($questions as $q)
                <div class="mb-6 md:mb-8 p-4 md:p-6 border-2 border-gray-100 rounded-xl bg-gray-50 hover:shadow-md transition duration-300">
                    <p class="font-bold text-lg md:text-xl text-gray-800 mb-3 border-b-2 border-gray-200 pb-2">
                        <span class="text-indigo-600 mr-2">{{ $loop->iteration }}.</span> {{ $q->text }}
                    </p>

                    {{-- Descriptive Question --}}
                    @if($q->type === 'descriptive')
                        <textarea 
                            name="answers[{{ $q->id }}][answer_text]" 
                            rows="3" 
                            class="w-full mt-3 border border-gray-300 rounded-lg p-3 md:p-4 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 shadow-sm permission-disabled"
                            placeholder="Type your detailed answer here..."
                            disabled></textarea>
                    @endif

                    {{-- Multiple Choice Questions --}}
                    @if(in_array($q->type, ['mcq_single', 'mcq_multiple']))
                        <div class="mt-4 space-y-3">
                            @foreach($q->options as $option)
                                <label class="flex items-start p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition duration-150 permission-disabled">
                                    <input 
                                        type="{{ $q->type === 'mcq_single' ? 'radio' : 'checkbox' }}" 
                                        name="answers[{{ $q->id }}][chosen_option_ids]{{ $q->type === 'mcq_single' ? '' : '[]' }}" 
                                        value="{{ $option->id }}" 
                                        class="mt-1 mr-3 h-4 w-4 md:h-5 md:w-5 text-indigo-600 border-gray-300 focus:ring-indigo-500 permission-disabled"
                                        disabled
                                    >
                                    <span class="text-sm md:text-base text-gray-700">{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <button id="submitBtn" type="button" class="w-full px-3 py-2 md:px-4 md:py-3 bg-indigo-600 text-white font-semibold text-base md:text-lg rounded-lg shadow-xl hover:bg-indigo-700 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:ring-opacity-50 permission-disabled" style="display: none;">
            Submit Exam
        </button>
    </form>
</div> <!-- end examContent -->

<!-- Permission Status (outside examContent) -->
<div id="permissionStatus" class="container mx-auto mt-4 md:mt-10 mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
    <p class="text-yellow-800 font-semibold">⚠️ Proctoring Permissions Required</p>
    <p class="text-yellow-700">Please allow camera and screen sharing permissions to continue with the exam. All inputs are disabled until permissions are granted.</p>
</div>

<!-- Hidden video previews (optional for debugging) -->
<video id="cameraPreview" autoplay muted style="width:200px; display:none;"></video>
<video id="screenPreview" autoplay style="width:200px; display:none;"></video>

<!-- Capture Status Indicator -->
<div id="captureStatus" style="
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-family: monospace;
    z-index: 9999;
    min-width: 200px;
    display: none;
">
    <div style="margin-bottom: 6px;">
        <span id="captureIndicator" style="
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #ff4444;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 1s infinite;
        "></span>
        <span id="captureText">Initializing...</span>
    </div>
    <div style="color: #aaa; font-size: 11px;">
        Frames: <span id="frameCounter">0</span>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    @keyframes success {
        0% { background: #ff4444; }
        100% { background: #44ff44; }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Safe DOM helpers to avoid null pointer errors
function safeSetText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}
function safeSetHTML(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = value;
}
function safeSetStyle(id, property, value) {
    const el = document.getElementById(id);
    if (el && el.style) el.style[property] = value;
}

// Custom alert functions to replace browser alerts
function showCustomAlert(message, type = 'info', title = 'Message') {
    const modalElement = document.getElementById('customAlertModal');
    if (!modalElement) {
        console.warn('Modal not found, fallback alert:', message);
        alert(title + '\n' + message);
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const modalHeader = document.getElementById('modalHeader');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('customAlertModalLabel');

    if (modalTitle) modalTitle.textContent = title;
    if (modalBody) modalBody.textContent = message;
    
    // Set header color based on type
    if (modalHeader) {
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
        const modalElement = document.getElementById('customConfirmModal');
        if (!modalElement) {
            console.warn('Confirm modal element not found; falling back to browser confirm.');
            resolve(window.confirm(`${title}\n\n${message}`));
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        const modalBody = document.getElementById('confirmModalBody');
        const modalTitle = document.getElementById('customConfirmModalLabel');
        const yesBtn = document.getElementById('confirmYesBtn');
        const noBtn = modalElement.querySelector('.btn-secondary');

        if (modalTitle) modalTitle.textContent = title;
        if (modalBody) modalBody.textContent = message;

        const cleanup = () => {
            if (yesBtn) yesBtn.removeEventListener('click', handleYes);
            if (noBtn) noBtn.removeEventListener('click', handleNo);
        };

        const handleYes = () => {
            modal.hide();
            cleanup();
            resolve(true);
        };

        const handleNo = () => {
            modal.hide();
            cleanup();
            resolve(false);
        };

        if (yesBtn) yesBtn.addEventListener('click', handleYes);
        if (noBtn) noBtn.addEventListener('click', handleNo);

        modal.show();
    });
}
</script>

<script>
    const durationMinutes = Number('{{ $exam->duration_minutes }}');
    const durationMs = durationMinutes * 60 * 1000;

    // Screenshot capture variables
    let cameraStream, screenStream;
    let cameraCanvas, screenCanvas;
    let screenshotInterval;
    let countdownInterval;
    let permissionsGranted = false;
    let examStarted = false;
    let autoSubmitTriggered = false;
    let frameNumber = 0;
    let capturedScreenshots = [];
    let totalUploaded = 0; // Track total screenshots uploaded during exam
    let SCREENSHOT_INTERVAL = 1500; // ms (capture every 1.5 seconds)

    // Disable all inputs if permissions not granted
    function toggleInputs(disabled) {
        console.log("🔄 TOGGLE INPUTS CALLED with disabled =", disabled);
        const elements = document.querySelectorAll('.permission-disabled');
        console.log("🔄 Found", elements.length, "permission-disabled elements");
        elements.forEach(el => {
            el.disabled = disabled;
        });
        
        // Specifically hide/show the submit button
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.style.display = disabled ? 'none' : 'block';
        }
        
        const examContent = document.getElementById('examContent');
        const permissionStatus = document.getElementById('permissionStatus');
        console.log("🔄 examContent element:", examContent);
        console.log("🔄 permissionStatus element:", permissionStatus);
        
        if (!examContent || !permissionStatus) {
            console.error("🔄 ERROR: examContent or permissionStatus element not found!");
            return;
        }
        
        console.log("🔄 Current examContent display:", examContent.style.display);
        console.log("🔄 Current permissionStatus display:", permissionStatus.style.display);
        if (disabled) {
            examContent.style.display = 'none';
            permissionStatus.style.display = 'block';
            console.log("🔄 SET: examContent=none, permissionStatus=block");
        } else {
            examContent.style.display = 'block';
            permissionStatus.style.display = 'none';
            console.log("🔄 SET: examContent=block, permissionStatus=none");
        }
        console.log("🔄 After setting - examContent display:", examContent.style.display);
        console.log("🔄 After setting - permissionStatus display:", permissionStatus.style.display);
    }

    // Request permissions
    async function requestPermissions() {
        try {
            console.log("🔐 requestPermissions: Starting permission request");

            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function' || typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
                console.error("❌ Media capture APIs unavailable on this origin or browser");
                document.getElementById('captureStatus').style.display = 'block';
                document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Camera and screen capture are not available on this page.</span>';
                showError('Your browser or the current site does not support camera and screen capture. Please use a secure origin (https:// or localhost) and refresh the page.', 'Capture Not Supported');
                return;
            }

            document.getElementById('captureStatus').style.display = 'block';
            document.getElementById('captureText').textContent = 'Requesting camera...';
            
            // Request camera permission and KEEP the stream
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
            document.getElementById('cameraPreview').srcObject = cameraStream;

            console.log("✅ Camera permission granted");
            document.getElementById('captureText').textContent = 'Requesting screen share...';

            // Request screen share permission and KEEP the stream
            screenStream = await navigator.mediaDevices.getDisplayMedia({ video: { cursor: 'always' } });
            document.getElementById('screenPreview').srcObject = screenStream;

            permissionsGranted = true;
            console.log("🔐 requestPermissions: All permissions granted, showing ready modal");
            document.getElementById('captureText').textContent = 'Permissions granted - confirm to start';
            
            // Show exam ready confirmation modal instead of immediately starting
            const readyModal = new bootstrap.Modal(document.getElementById('examReadyModal'));
            readyModal.show();
            
            // Don't start recording yet - wait for user confirmation
        } catch (err) {
            console.error("❌ Permission denied:", err);
            permissionsGranted = false;
            document.getElementById('captureIndicator').style.background = '#ff6666';
            toggleInputs(true);
            document.getElementById('captureStatus').style.display = 'block';
            
            // Determine which permission failed
            if (err.name === 'NotAllowedError' || err.name === 'NotFoundError' || err.name === 'NotReadableError') {
                if (err.message.includes('video') || err.message.includes('camera')) {
                    document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Camera permission denied</span>';
                    showError('Camera permission is required to take this exam. Please allow camera access and refresh the page.', 'Camera Required');
                } else if (err.message.includes('screen') || err.message.includes('display')) {
                    document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Screen share permission denied</span>';
                    showError('Screen sharing permission is required to take this exam. Please allow screen sharing and refresh the page.', 'Screen Share Required');
                } else {
                    document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Permissions denied</span>';
                    showError('Camera and screen sharing permissions are required to take this exam. Please allow both and refresh the page.', 'Permissions Required');
                }
            } else {
                document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Permission error</span>';
                showError('An error occurred while requesting permissions. Please refresh the page and try again.', 'Permission Error');
            }
        } finally {
            // Hide the permission overlay after permission request completes
            const overlay = document.getElementById('permissionOverlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
    }

    // Timer countdown
    function startTimer() {
        let timeLeft = durationMs / 1000;
        const timerEl = document.getElementById('timer');

        countdownInterval = setInterval(() => {
            const mins = Math.floor(timeLeft / 60);
            const secs = timeLeft % 60;
            timerEl.textContent = `Time left: ${mins}:${secs < 10 ? '0' : ''}${secs}`;
            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(countdownInterval);
                submitExam();
            }
        }, 1000);
    }

    // Disable right-click / copy-paste / shortcuts
    function disableInteractions() {
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('keydown', e => {
            if (e.ctrlKey && ['c', 'v', 'x', 'u'].includes(e.key.toLowerCase())) e.preventDefault();
            // Prevent F12, Ctrl+Shift+I (inspect)
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                autoSubmitExam('Attempted to open developer tools', true);
            }
        });
        // Prevent leaving tab
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && examStarted && !autoSubmitTriggered) {
                autoSubmitExam('Left the exam tab', true);
            }
        });
        // Prevent closing window - submit exam when attempting to leave
        window.addEventListener('beforeunload', e => {
            if (examStarted && !autoSubmitTriggered) {
                autoSubmitExam('Attempted to leave the exam page', false);
            }
        });

        // Detect loss of focus / tab switch as an anti-cheat trigger
        window.addEventListener('blur', () => {
            if (examStarted && !autoSubmitTriggered) {
                autoSubmitExam('Window lost focus / switched tab', true);
            }
        });
    }

    // Auto submit exam (for security violations - immediate submit)
    function autoSubmitExam(reason, redirect = false) {
        if (autoSubmitTriggered) {
            return;
        }
        autoSubmitTriggered = true;
        console.log(`🚨 Auto-submitting exam immediately: ${reason}`);
        
        // Collect current form data
        const form = document.getElementById('examForm');
        const formData = new FormData(form);
        formData.append('auto_submit_reason', reason);
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }
        
        // Use sendBeacon for reliable submission during page unload
        const url = form.action;
        const data = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            if (typeof value === 'string') {
                data.append(key, value);
            } else {
                // For files, we can't send via sendBeacon easily
                data.append(key, ''); // Placeholder
            }
        }
        
        navigator.sendBeacon(url, data);
        
        // Also try regular fetch as backup with keepalive
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
            },
            keepalive: true // Keep alive for page unload
        })
        .then(response => {
            if (response.ok && redirect) {
                // Redirect to submitted page (only for tab switch, not page unload)
                window.location.href = '/exam/{{ $exam->uuid }}/submitted';
            }
        })
        .catch(() => {
            // Ignore errors during unload
        });
    }

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload(true);
        }
    });

    // Check if exam has been submitted every 30 seconds
    setInterval(function() {
        fetch('/exam/{{ $exam->uuid }}/check-submission', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.submitted) {
                // Exam has been submitted, redirect to submitted page
                window.location.href = '/exam/{{ $exam->uuid }}/submitted';
            }
        })
        .catch(error => {
            console.log('Error checking submission status:', error);
        });
    }, 30000); // Check every 30 seconds

    // Submit exam function
    async function submitExam() {
        console.log("📤 submitExam: Called, permissionsGranted=" + permissionsGranted);
        if (!permissionsGranted) {
            console.error("📤 submitExam: permissions not granted, returning");
            return;
        }

        const form = document.getElementById('examForm');
        if (!form) {
            console.error("📤 submitExam: Form element not found");
            return;
        }

        const formData = new FormData(form);
        formData.append('auto_submit_reason', 'Time expired or session ended');

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken && !formData.has('_token')) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }

        const url = form.action;
        const beaconData = new URLSearchParams();
        for (let [key, value] of formData.entries()) {
            beaconData.append(key, typeof value === 'string' ? value : '');
        }

        navigator.sendBeacon(url, beaconData);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                },
                keepalive: true
            });

            if (response.ok) {
                window.location.href = '/exam/{{ $exam->uuid }}/submitted';
            }
        } catch (err) {
            console.error("📤 submitExam fetch error:", err);
        }
    }

    // Start screenshot capture
    async function startRecording() {
        console.log("🎥 startRecording: Called, permissionsGranted=" + permissionsGranted);
        if (!permissionsGranted) {
            console.error("🎥 startRecording: permissions not granted, returning");
            return;
        }

        try {
            examStarted = true;
            frameNumber = 0;
            capturedScreenshots = [];
            document.getElementById('captureText').textContent = 'Starting capture...';
            document.getElementById('captureIndicator').style.animation = 'pulse 1s infinite';
            console.log("🎥 startRecording: Initialized variables");

            // Streams are already obtained in requestPermissions() - just verify they exist
            console.log("🎥 startRecording: Using existing streams (already requested)");
            
            if (!cameraStream || !screenStream) {
                throw new Error('Streams not available - permissions may have been revoked');
            }

            document.getElementById('captureText').textContent = 'Capturing (screen+face)';
            console.log("✅ Screen capture initiated");

            // If screen share ends early
            screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                console.warn("⚠️ Screen sharing stopped early!");
                autoSubmitExam('Screen sharing stopped', true);
            });

            // Start capturing screenshots
            console.log("🎥 startRecording: About to call startScreenshotCapture");
            startScreenshotCapture();
            console.log("🎥 startRecording: About to call startTimer");
            startTimer();
            console.log("🎥 startRecording: About to call disableInteractions");
            disableInteractions();
            console.log("🎥 startRecording: startRecording complete");

        } catch (err) {
            console.error("❌ Error starting capture:", err);
            document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Error: ' + err.message + '</span>';
            document.getElementById('captureIndicator').style.background = '#ff6666';
            
            // Check if it's a screen sharing error
            if (err.name === 'NotAllowedError' || err.message.includes('screen') || err.message.includes('display')) {
                showError('Screen sharing permission is required to take this exam. Please allow screen sharing and refresh the page.', 'Screen Share Required');
            } else {
                showError('Camera permission required to take this exam.', 'Permission Error');
            }
        }
    }

    // Capture screenshots periodically
    function startScreenshotCapture() {
        console.log("📸 startScreenshotCapture: Starting at " + SCREENSHOT_INTERVAL + "ms interval");
        console.log("📸 startScreenshotCapture: screenStream=" + (screenStream ? "YES" : "NO") + ", cameraStream=" + (cameraStream ? "YES" : "NO"));

        screenshotInterval = setInterval(async () => {
            try {
                console.log("📸 Screenshot capture tick: about to capture frame #" + frameNumber);
                
                if (screenStream) {
                    console.log("📸 Capturing screen image");
                    const screenImage = await captureStreamToImage(screenStream, 'screen');
                    if (screenImage) {
                        console.log("📸 Screen image captured, pushing to array");
                        capturedScreenshots.push(screenImage);
                    } else {
                        console.warn("📸 Screen image capture returned null");
                    }
                }

                if (cameraStream) {
                    console.log("📸 Capturing face image");
                    const faceImage = await captureStreamToImage(cameraStream, 'face');
                    if (faceImage) {
                        console.log("📸 Face image captured, pushing to array");
                        capturedScreenshots.push(faceImage);
                    } else {
                        console.warn("📸 Face image capture returned null");
                    }
                }

                frameNumber++;
                document.getElementById('frameCounter').textContent = `${capturedScreenshots.length} pending, ${totalUploaded} uploaded`;
                console.log(`📸 Frame ${frameNumber} complete (pending: ${capturedScreenshots.length}, uploaded: ${totalUploaded})`);

                // Upload in batches during exam to avoid large final upload
                if (capturedScreenshots.length >= UPLOAD_BATCH_SIZE && capturedScreenshots.length % UPLOAD_BATCH_SIZE === 0) {
                    const batch = capturedScreenshots.splice(0, UPLOAD_BATCH_SIZE);
                    console.log(`📤 Triggering batch upload of ${batch.length} screenshots`);
                    uploadBatch(batch).catch(err => console.error("Background batch upload failed:", err));
                }
            } catch (err) {
                console.error("❌ Screenshot error:", err);
            }
        }, SCREENSHOT_INTERVAL);
        console.log("📸 startScreenshotCapture: Interval set, screenshotInterval=" + screenshotInterval);
    }

    // Stream to image conversion
    function captureStreamToImage(stream, type) {
        return new Promise((resolve) => {
            try {
                console.log(`🎬 captureStreamToImage: Starting for type=${type}`);
                const video = document.createElement('video');
                video.srcObject = stream;
                console.log(`🎬 captureStreamToImage: video element created and stream assigned`);
                
                video.onloadedmetadata = () => {
                    console.log(`🎬 captureStreamToImage: metadata loaded for ${type}, videoWidth=${video.videoWidth}, videoHeight=${video.videoHeight}`);
                    video.play();
                    const drawFrame = () => {
                        try {
                            console.log(`🎬 captureStreamToImage: drawFrame called for ${type}`);
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth || 640;
                            canvas.height = video.videoHeight || 480;
                            console.log(`🎬 captureStreamToImage: canvas size ${canvas.width}x${canvas.height}`);
                            
                            canvas.getContext('2d').drawImage(video, 0, 0);
                            console.log(`🎬 captureStreamToImage: image drawn on canvas`);
                            
                            canvas.toBlob((blob) => {
                                if (blob) {
                                    console.log(`🎬 captureStreamToImage: blob created, size=${blob.size} bytes`);
                                    const result = {
                                        type: type,
                                        frame_number: frameNumber,
                                        timestamp: new Date().toISOString().slice(0, 19).replace('T', ' '),
                                        file: new File([blob], `${type}_${frameNumber}.jpg`, { type: 'image/jpeg' })
                                    };
                                    console.log(`🎬 captureStreamToImage: resolving with file for ${type}`);
                                    resolve(result);
                                } else {
                                    console.warn(`⚠️ captureStreamToImage: blob is null for ${type}`);
                                    resolve(null);
                                }
                            }, 'image/jpeg', 0.8);
                        } catch (err) {
                            console.error("❌ Canvas error:", err);
                            resolve(null);
                        }
                    };
                    if (video.readyState >= video.HAVE_CURRENT_DATA) {
                        console.log(`🎬 captureStreamToImage: readyState OK, calling drawFrame`);
                        drawFrame();
                    } else {
                        console.log(`🎬 captureStreamToImage: readyState not ready, will retry in 100ms`);
                        setTimeout(drawFrame, 100);
                    }
                };
                video.onerror = () => {
                    console.error(`❌ captureStreamToImage: video onerror for ${type}`);
                    resolve(null);
                };
            } catch (err) {
                console.error("❌ Capture error:", err);
                resolve(null);
            }
        });
    }

    // Stop screenshot capture
    async function stopRecording() {
        console.log("⏹️ stopRecording: Called with " + capturedScreenshots.length + " screenshots");
        clearInterval(countdownInterval);
        clearInterval(screenshotInterval);
        console.log("⏹️ stopRecording: Intervals cleared");
        
        // Stop all streams
        if (cameraStream) {
            console.log("⏹️ stopRecording: Stopping camera stream");
            cameraStream.getTracks().forEach(track => track.stop());
        }
        if (screenStream) {
            console.log("⏹️ stopRecording: Stopping screen stream");
            screenStream.getTracks().forEach(track => track.stop());
        }

        console.log(`⏹️ Recording stopped. Captured ${capturedScreenshots.length} images.`);
        console.log("⏹️ stopRecording: About to call uploadScreenshots");
        await uploadScreenshots();
        console.log("⏹️ stopRecording: uploadScreenshots completed");
    }

    // Upload captured screenshots
    const UPLOAD_BATCH_SIZE = 10; // Upload every 10 screenshots during exam

    // Function to upload a batch of screenshots asynchronously
    async function uploadBatch(screenshots) {
        if (!screenshots || screenshots.length === 0) return;

        console.log(`📤 uploadBatch: Uploading ${screenshots.length} screenshots`);
        safeSetText('captureText', `Uploading batch of ${screenshots.length}...`);

        try {
            const formData = new FormData();
            screenshots.forEach((screenshot, index) => {
                if (screenshot && screenshot.file) {
                    formData.append(`screenshots[${index}][image]`, screenshot.file);
                    formData.append(`screenshots[${index}][type]`, screenshot.type);
                    formData.append(`screenshots[${index}][frame_number]`, screenshot.frame_number);
                    formData.append(`screenshots[${index}][timestamp]`, screenshot.timestamp);
                }
            });

            const studentId = '{{ $studentSession["student_id"] ?? session("student_id") ?? "" }}';
            const examId = '{{ $exam->id }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            if (!studentId || !examId) {
                throw new Error('Missing session data');
            }

            formData.append('student_id', studentId);
            formData.append('exam_id', examId);

            const uploadUrl = "{{ route('upload.proctor.screenshots', ['uuid' => $exam->uuid]) }}";

            const response = await Promise.race([
                fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: formData
                }),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Batch upload timeout')), 30000))
            ]);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Upload failed');
            }

            console.log(`✅ Batch uploaded ${screenshots.length} screenshots`);
            totalUploaded += screenshots.length;
            safeSetText('captureText', `Total uploaded: ${totalUploaded}`);

        } catch (err) {
            console.error("❌ Batch upload failed:", err.message);
            // Log but don't interrupt exam - screenshots are still in array if needed
        }
    }

    async function uploadScreenshots() {
        console.log(`📤 uploadScreenshots triggered (remaining count=${capturedScreenshots.length})`);
        safeSetText('captureText', `Uploading remaining ${capturedScreenshots.length} frames...`);

        if (capturedScreenshots.length === 0) {
            console.warn("⚠️ No remaining screenshots to upload");
            safeSetHTML('captureStatus', '<span style="color: #66ff66;">✅ All frames already uploaded</span>');
            safeSetStyle('captureIndicator', 'background', '#66ff66');
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            // Prepare for chunked uploading to obey PHP max_file_uploads (default 20)
            const MAX_FILES_PER_REQUEST = 19; // Increased from 18 for faster uploads (still under PHP max_file_uploads=20)
            const totalScreenshots = capturedScreenshots.length;

            // First, test if server is responding
            console.log("📤 Testing server connectivity...");
            try {
                const testResponse = await Promise.race([
                    fetch("{{ route('test.upload.endpoint', ['uuid' => $exam->uuid]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                        },
                        body: JSON.stringify({test: true})
                    }),
                    new Promise((_, reject) => 
                        setTimeout(() => reject(new Error('Connectivity test timeout')), 5000)
                    )
                ]);
                console.log("✅ Server connectivity test passed:", testResponse.status);
            } catch (testErr) {
                console.error("⚠️ Server connectivity test failed:", testErr.message);
                throw new Error(`Server unreachable: ${testErr.message.substring(0, 40)}`);
            }

            const studentId = '{{ $studentSession["student_id"] ?? session("student_id") ?? "" }}';
            const examId = '{{ $exam->id }}';

            if (!studentId || studentId.trim() === '') {
                throw new Error('Missing student_id - session may have expired');
            }
            if (!examId || examId.trim() === '') {
                throw new Error('Missing exam_id - invalid exam');
            }

            let uploadedTotal = 0;
            const totalChunks = Math.max(1, Math.ceil(totalScreenshots / MAX_FILES_PER_REQUEST));
            let chunkIndex = 0;

            while (capturedScreenshots.length > 0) {
                chunkIndex++;
                const chunkScreenshots = capturedScreenshots.splice(0, MAX_FILES_PER_REQUEST);
                console.log(`📤 Processing chunk ${chunkIndex}/${totalChunks} with ${chunkScreenshots.length} files`);

                const formData = new FormData();
                let filesAdded = 0;

                chunkScreenshots.forEach((screenshot, index) => {
                    if (!screenshot || !screenshot.file) {
                        console.warn(`⚠️ Missing screenshot for chunk ${chunkIndex} index ${index}`, screenshot);
                        return;
                    }
                    const globalIndex = index;
                    formData.append(`screenshots[${globalIndex}][image]`, screenshot.file);
                    formData.append(`screenshots[${globalIndex}][type]`, screenshot.type);
                    formData.append(`screenshots[${globalIndex}][frame_number]`, screenshot.frame_number);
                    formData.append(`screenshots[${globalIndex}][timestamp]`, screenshot.timestamp);
                    filesAdded++;
                });

                if (filesAdded === 0) {
                    continue;
                }

                formData.append('student_id', studentId);
                formData.append('exam_id', examId);

                const uploadUrl = "{{ route('upload.proctor.screenshots', ['uuid' => $exam->uuid]) }}";
                console.log(`📤 Uploading chunk ${chunkIndex}/${totalChunks} to ${uploadUrl} with ${filesAdded} files`);

                let response;
                try {
                    response = await Promise.race([
                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                            },
                            body: formData
                        }),
                        new Promise((_, reject) => setTimeout(() => reject(new Error('Upload timeout (60s)')), 60000))
                    ]);
                } catch (fetchErr) {
                    console.error("📤 Fetch error (network level):", fetchErr.message);
                    throw new Error(`Network error: ${fetchErr.message.substring(0, 50)}`);
                }

                console.log(`📤 HTTP status for chunk ${chunkIndex + 1}/${totalChunks}:`, response.status, response.statusText);
                safeSetText('captureText', `Upload response: ${response.status}`);

                if (!response.ok) {
                    let responseText = '';
                    try { responseText = await response.text(); } catch (_) { responseText = 'Unable to read response'; }
                    throw new Error(`HTTP ${response.status}: ${responseText.substring(0, 100)}`);
                }

                let data;
                try { data = await response.json(); } catch (jsonErr) {
                    throw new Error(`Invalid response format: ${jsonErr.message.substring(0, 50)}`);
                }

                if (!data.success) {
                    const errorMsg = data.message + (data.errors ? ' | ' + JSON.stringify(data.errors) : '');
                    throw new Error(`Upload failed: ${errorMsg}`);
                }

                uploadedTotal += filesAdded;
                totalUploaded += filesAdded;
                console.log(`✅ Chunk ${chunkIndex + 1}/${totalChunks} uploaded ${filesAdded} images`);
                const progressPercent = Math.round((chunkIndex + 1) / totalChunks * 100);
                safeSetHTML('captureStatus', `<span style="color: #66ff66;">Uploading... ${progressPercent}% (${uploadedTotal}/${totalScreenshots})</span>`);
            }

            if (uploadedTotal === 0) {
                throw new Error('No screenshots were uploaded.');
            }

            safeSetHTML('captureStatus', `<span style="color: #66ff66;">✅ Uploaded remaining ${uploadedTotal} of ${totalScreenshots} screenshots (total: ${totalUploaded + uploadedTotal})</span>`);
            safeSetStyle('captureIndicator', 'background', '#66ff66');
            const indicator = document.getElementById('captureIndicator');
            if (indicator) indicator.style.animation = 'none';

        } catch (err) {
            console.error("⚠️ Screenshot upload error:", err.message, err);
            safeSetHTML('captureStatus', '<span style="color: #ff6666;">❌ ' + err.message.substring(0, 150) + '</span>');
            safeSetStyle('captureIndicator', 'background', '#ff6666');
            throw err;  // rethrow so submit flow can handle
        }
    }

    // Submit button confirmation
    document.getElementById('submitBtn').addEventListener('click', async (e) => {
        console.log("🔘 Submit button clicked");
        e.preventDefault(); // Prevent form submission

        const confirmed = await showCustomConfirm(
            'Are you sure you want to submit the exam? This action cannot be undone.',
            'Submit Exam'
        );

        console.log("🔘 Confirm result:", confirmed);
        if (!confirmed) {
            console.log("🔘 User cancelled submit");
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        }

        try {
            console.log("🔘 About to stop recording and upload");
            await stopRecording();

            console.log("✅ Upload completed successfully, now submitting exam");
            safeSetHTML('captureStatus', '<span style="color: #66ff66;">✅ Screenshots uploaded, submitting exam...</span>');

            const form = document.getElementById('examForm');
            if (!form) {
                throw new Error('Form element not found during submission.');
            }

            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken && !formData.has('_token')) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }

            const submitUrl = form.action;
            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                }
            });

            if (!response.ok) {
                const bodyText = await response.text();
                throw new Error(`Submit failed with status ${response.status}: ${bodyText.substring(0, 200)}`);
            }

            // Replace the current history entry so back does not go to POST /submit.
            window.location.replace('/exam/{{ $exam->uuid }}/submitted');

        } catch (uploadErr) {
            console.error("❌ Upload failed:", uploadErr);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Exam';
            }
            await showCustomAlert('Upload failed: ' + uploadErr.message + '. Please try again.', 'Upload Error');
        }
    });

    // Start Exam button handler
    document.getElementById('startExamBtn').addEventListener('click', () => {
        console.log("🎯 Start Exam button clicked");
        
        // Hide the modal
        const readyModal = bootstrap.Modal.getInstance(document.getElementById('examReadyModal'));
        readyModal.hide();
        
        // Now start the exam
        console.log("🎯 Starting exam after user confirmation");
        toggleInputs(false);
        
        // Explicitly ensure submit button is visible and enabled
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.style.display = 'block';
            submitBtn.textContent = 'Submit Exam';
        }
        
        // Show exam content
        const examContent = document.getElementById('examContent');
        const permissionStatus = document.getElementById('permissionStatus');
        if (examContent) {
            examContent.style.display = 'block';
            console.log("🧪 examContent set to block");
        } else {
            console.error("🧪 examContent element not found!");
        }
        if (permissionStatus) {
            permissionStatus.style.display = 'none';
            console.log("🧪 permissionStatus set to none");
        } else {
            console.error("🧪 permissionStatus element not found!");
        }
        
        // Start recording and timer
        startRecording();
    });

    window.onload = async () => {
        await submissionCheckPromise;
        if (!submissionCheckProceed) {
            return;
        }

        console.log("🟢 PAGE LOAD: window.onload fired");
        document.getElementById('captureStatus').style.display = 'block';
        document.getElementById('captureText').textContent = 'Waiting for permissions...';
        console.log("🟢 PAGE LOAD: Calling toggleInputs(true) to disable inputs");
        toggleInputs(true); // Start with inputs disabled
        console.log("🟢 PAGE LOAD: Calling requestPermissions()");
        await requestPermissions();
        console.log("🟢 PAGE LOAD: requestPermissions() completed");
        // Note: Recording will start when exam actually begins (permissions granted)
    };

    (function preventExamBackNavigation() {
        function preventBack() {
            window.history.pushState(null, '', window.location.href);
        }

        for (let i = 0; i < 25; i++) {
            preventBack();
        }

        window.addEventListener('popstate', function () {
            preventBack();
            showCustomAlert('Back navigation is disabled while the exam is active. Please continue with your exam.', 'Navigation Disabled');
        });
    })();
</script>


</body>
</html>
