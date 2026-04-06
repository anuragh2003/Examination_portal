<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $exam->name }} - Take Exam</title>
    <!-- In your layout or before proctor-screenshots.blade.php -->
<script src="{{ asset('js/screenshot-gallery.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">

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

<div class="max-w-4xl mx-auto mt-10 bg-white p-8 shadow-2xl rounded-xl border border-gray-100">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-2 border-b pb-2">{{ $exam->name }}</h1>
    <p class="mb-4 text-gray-500 italic">{{ $exam->description }}</p>
    <div class="flex justify-between items-center mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <p class="font-bold text-lg text-blue-700">Duration: <span class="text-blue-900">{{ $exam->duration_minutes }} minutes</span></p>
        <p id="timer" class="text-2xl text-red-700 font-extrabold tracking-wide bg-red-100 px-3 py-1 rounded-full shadow-inner"></p>
    </div>

    <!-- Permission Status -->
    <div id="permissionStatus" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
        <p class="text-yellow-800 font-semibold">⚠️ Proctoring Permissions Required</p>
        <p class="text-yellow-700">Please allow camera and screen sharing permissions to continue with the exam. All inputs are disabled until permissions are granted.</p>
    </div>

    <form id="examForm" method="POST" action="{{ route('exam.submit', $exam->uuid) }}">
        @csrf

        @foreach($questions as $q)
            <div class="mb-8 p-6 border-2 border-gray-100 rounded-xl bg-gray-50 hover:shadow-md transition duration-300">
                <p class="font-bold text-xl text-gray-800 mb-3 border-b-2 border-gray-200 pb-2">
                    <span class="text-indigo-600 mr-2">{{ $loop->iteration }}.</span> {{ $q->text }}
                </p>

                {{-- Descriptive Question --}}
                @if($q->type === 'descriptive')
                    <textarea 
                        name="answers[{{ $q->id }}][answer_text]" 
                        rows="4" 
                        class="w-full mt-3 border border-gray-300 rounded-lg p-4 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 shadow-sm permission-disabled"
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
                                    class="mt-1 mr-3 h-5 w-5 text-indigo-600 border-gray-300 focus:ring-indigo-500 permission-disabled"
                                    disabled
                                >
                                <span class="text-gray-700">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <button id="submitBtn" type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white font-semibold text-lg rounded-lg shadow-xl hover:bg-indigo-700 transition duration-300 transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:ring-opacity-50 permission-disabled" disabled>
            Submit Exam
        </button>
    </form>
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
        const elements = document.querySelectorAll('.permission-disabled');
        elements.forEach(el => {
            el.disabled = disabled;
        });
        document.getElementById('permissionStatus').style.display = disabled ? 'block' : 'none';
    }

    // Request permissions
    async function requestPermissions() {
        try {
            console.log("🔐 requestPermissions: Starting permission request");
            document.getElementById('captureStatus').style.display = 'block';
            document.getElementById('captureText').textContent = 'Requesting camera...';
            
            // Request camera permission only (screen sharing will be requested when starting recording)
            const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            cameraStream.getTracks().forEach(track => track.stop()); // Stop immediately after permission

            permissionsGranted = true;
            console.log("🔐 requestPermissions: Permission granted, calling toggleInputs(false)");
            document.getElementById('captureText').textContent = 'Permission granted';
            toggleInputs(false);
            console.log("✅ Camera permission granted");
            console.log("🔐 requestPermissions: About to call startRecording()");
            startRecording();
        } catch (err) {
            console.error("❌ Camera permission denied:", err);
            permissionsGranted = false;
            document.getElementById('captureStatus').innerHTML = '<span style="color: #ff6666;">❌ Camera permission denied</span>';
            document.getElementById('captureIndicator').style.background = '#ff6666';
            toggleInputs(true);
            showError('Camera permission is required to take this exam. Please allow camera access and refresh the page.', 'Permission Required');
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
                window.location.href = `{{ route("student.exam-submitted", $exam->uuid) }}`;
            }
        })
        .catch(() => {
            // Ignore errors during unload
        });
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

            // Get camera stream
            console.log("🎥 startRecording: Requesting camera stream");
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('cameraPreview').srcObject = cameraStream;
            document.getElementById('captureText').textContent = 'Camera ready';
            console.log("🎥 startRecording: Camera stream obtained");

            // Get screen stream (optional)
            try {
                console.log("🎥 startRecording: Requesting screen stream");
                document.getElementById('captureText').textContent = 'Waiting for screen...';
                screenStream = await navigator.mediaDevices.getDisplayMedia({ video: { cursor: 'always' } });
                document.getElementById('screenPreview').srcObject = screenStream;
                document.getElementById('captureText').textContent = 'Capturing (screen+face)';
                console.log("✅ Screen capture initiated");

                // If screen share ends early
                screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                    console.warn("⚠️ Screen sharing stopped early!");
                    autoSubmitExam('Screen sharing stopped', true);
                });
            } catch (screenErr) {
                console.warn("⚠️ Screen sharing denied (optional):", screenErr.message);
                document.getElementById('captureText').textContent = 'Capturing (face only)';
            }

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
            showError('Camera permission required to take this exam.', 'Permission Error');
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

            if (!studentId || !examId) {
                throw new Error('Missing session data');
            }

            formData.append('student_id', studentId);
            formData.append('exam_id', examId);

            const uploadUrl = "{{ route('api.upload.proctor.screenshots', ['uuid' => $exam->uuid]) }}";

            const response = await Promise.race([
                fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
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
            // Prepare for chunked uploading to obey PHP max_file_uploads (default 20)
            const MAX_FILES_PER_REQUEST = 19; // Increased from 18 for faster uploads (still under PHP max_file_uploads=20)
            const totalScreenshots = capturedScreenshots.length;

            // First, test if server is responding
            console.log("📤 Testing server connectivity...");
            try {
                const testResponse = await Promise.race([
                    fetch("{{ route('api.test.upload.endpoint', ['uuid' => $exam->uuid]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
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

            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const start = chunkIndex * MAX_FILES_PER_REQUEST;
                const chunkScreenshots = capturedScreenshots.slice(start, start + MAX_FILES_PER_REQUEST);

                if (!chunkScreenshots.length) break;
                console.log(`📤 Processing chunk ${chunkIndex + 1}/${totalChunks} with ${chunkScreenshots.length} files`);

                const formData = new FormData();
                let filesAdded = 0;

                chunkScreenshots.forEach((screenshot, index) => {
                    if (!screenshot || !screenshot.file) {
                        console.warn(`⚠️ Missing screenshot for chunk ${chunkIndex + 1} index ${index}`, screenshot);
                        return;
                    }
                    const globalIndex = start + index;
                    formData.append(`screenshots[${globalIndex}][image]`, screenshot.file);
                    formData.append(`screenshots[${globalIndex}][type]`, screenshot.type);
                    formData.append(`screenshots[${globalIndex}][frame_number]`, screenshot.frame_number);
                    formData.append(`screenshots[${globalIndex}][timestamp]`, screenshot.timestamp);
                    filesAdded++;
                });

                if (filesAdded === 0) continue;

                formData.append('student_id', studentId);
                formData.append('exam_id', examId);

                const uploadUrl = "{{ route('api.upload.proctor.screenshots', ['uuid' => $exam->uuid]) }}";
                console.log(`📤 Uploading chunk ${chunkIndex + 1}/${totalChunks} to ${uploadUrl} with ${filesAdded} files`);

                let response;
                try {
                    response = await Promise.race([
                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json'
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
            form.submit();

        } catch (uploadErr) {
            console.error("❌ Upload failed:", uploadErr);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Exam';
            }
            await showCustomAlert('Upload failed: ' + uploadErr.message + '. Please try again.', 'Upload Error');
        }
    });

    window.onload = async () => {
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
</script>


</body>
</html>
