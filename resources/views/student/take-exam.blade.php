<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $exam->name }} - Take Exam</title>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
</script>

<script>
    const durationMinutes = Number('{{ $exam->duration_minutes }}');
    const durationMs = durationMinutes * 60 * 1000;

    let cameraRecorder, screenRecorder;
    let cameraChunks = [], screenChunks = [];
    let countdownInterval;
    let permissionsGranted = false;
    let examStarted = false;

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
            // Request camera permission only (screen sharing will be requested when starting recording)
            const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            cameraStream.getTracks().forEach(track => track.stop()); // Stop immediately after permission

            permissionsGranted = true;
            toggleInputs(false);
            console.log("✅ Camera permission granted");
            startRecording();
        } catch (err) {
            console.error("❌ Camera permission denied:", err);
            permissionsGranted = false;
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
            if (document.hidden && examStarted) {
                autoSubmitExam('Left the exam tab', true);
            }
        });
        // Prevent closing window - submit exam when attempting to leave
        window.addEventListener('beforeunload', e => {
            if (examStarted) {
                autoSubmitExam('Attempted to leave the exam page', false);
            }
        });
    }

    // Auto submit exam (for security violations - immediate submit)
    function autoSubmitExam(reason, redirect = false) {
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

    // Normal submit exam (waits for video upload)
    async function submitExam() {
        console.log("⏹️ Stopping recordings & uploading before submit...");
        await stopRecording();
        const form = document.getElementById('examForm');
        form.submit();
    }

    // Start recording
    async function startRecording() {
        if (!permissionsGranted) return;

        try {
            examStarted = true;

            // Camera (with audio)
            const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            document.getElementById('cameraPreview').srcObject = cameraStream;

            // Screen (video only - audio may cause issues)
            const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            document.getElementById('screenPreview').srcObject = screenStream;

            // Camera recorder
            cameraRecorder = new MediaRecorder(cameraStream, { mimeType: 'video/webm; codecs=vp9' });
            cameraRecorder.ondataavailable = e => { if (e.data.size > 0) cameraChunks.push(e.data); };
            cameraRecorder.start(1000);

            // Screen recorder
            screenRecorder = new MediaRecorder(screenStream, { mimeType: 'video/webm; codecs=vp9' });
            screenRecorder.ondataavailable = e => { if (e.data.size > 0) screenChunks.push(e.data); };
            screenRecorder.start(1000);

            console.log("✅ Recording started: Camera + Screen");

            startTimer();
            disableInteractions();

            // If screen share ends early
            screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                console.warn("⚠️ Screen sharing stopped early!");
                autoSubmitExam('Screen sharing stopped', true);
            });

        } catch (err) {
            console.error("❌ Error starting proctoring:", err);
            autoSubmitExam('Failed to start proctoring', true);
        }
    }

    // Stop a recorder
    function stopRecorder(recorder, chunks) {
        return new Promise(resolve => {
            if (!recorder || recorder.state === 'inactive') return resolve();
            recorder.onstop = () => {
                console.log("🛑 Recorder stopped:", recorder.mimeType, "Chunks:", chunks.length);
                resolve();
            };
            recorder.stop();
        });
    }

    // Stop all recording
    async function stopRecording() {
        clearInterval(countdownInterval);
        await stopRecorder(cameraRecorder, cameraChunks);
        await stopRecorder(screenRecorder, screenChunks);
        await uploadVideos();
    }

    // Upload videos
    async function uploadVideos() {
        const cameraBlob = new Blob(cameraChunks, { type: 'video/webm' });
        const screenBlob = new Blob(screenChunks, { type: 'video/webm' });

        console.log("🎥 Camera Blob size:", cameraBlob.size, "chunks:", cameraChunks.length);
        console.log("🖥️ Screen Blob size:", screenBlob.size, "chunks:", screenChunks.length);

        if (cameraBlob.size === 0 && screenBlob.size === 0) {
            console.error("❌ No video data to upload");
            return;
        }

        const formData = new FormData();
        if (cameraBlob.size > 0) formData.append('camera_video', cameraBlob, 'camera.webm');
        if (screenBlob.size > 0) formData.append('screen_video', screenBlob, 'screen.webm');

        const studentId = '{{ $studentSession["student_id"] ?? auth()->user()->id }}';
        formData.append('student_id', studentId);
        formData.append('exam_id', '{{ $exam->id }}');

        try {
            const res = await fetch("{{ route('upload.proctor.videos', ['uuid' => $exam->uuid]) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                body: formData
            });

            const data = await res.json();
            console.log("📤 Proctor upload response:", data);

            if (!data.success) {
                console.error("❌ Upload failed:", data.message);
            } else {
                console.log("✅ Videos uploaded successfully!");
            }
        } catch (err) {
            console.error("❌ Video upload failed:", err);
        }
    }

    // Submit button confirmation
    document.getElementById('submitBtn').addEventListener('click', async (e) => {
        e.preventDefault(); // Prevent form submission
        
        const confirmed = await showCustomConfirm(
            'Are you sure you want to submit the exam? This action cannot be undone.',
            'Submit Exam'
        );
        
        if (confirmed) {
            // Proceed with form submission
            document.getElementById('examForm').dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    // Ensure form submits only after videos uploaded (for manual submit)
    document.getElementById('examForm').addEventListener('submit', async (e) => {
        // If this is an auto-submit (has reason field), don't wait for videos
        const reasonField = e.target.querySelector('input[name="auto_submit_reason"]');
        if (reasonField) {
            // Auto-submit, let it proceed immediately
            return;
        }
        
        // Manual submit, wait for videos
        e.preventDefault();
        await submitExam();
    });

    // Start on page load - request permissions first
    window.onload = async () => {
        toggleInputs(true); // Start with inputs disabled
        await requestPermissions();
        // Note: Recording will start when exam actually begins (permissions granted)
    };
</script>


</body>
</html>
