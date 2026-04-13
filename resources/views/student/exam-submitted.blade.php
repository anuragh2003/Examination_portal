<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Exam Submitted</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .success-card {
            background-color: #ffffff;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px 0 rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="success-card max-w-2xl w-full p-8 md:p-12">
    <!-- Success Icon -->
    <div class="text-center mb-8">
        <svg class="mx-auto h-20 w-20 text-green-500 mb-6 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-3">Exam Submitted!</h1>
        <p class="text-xl text-gray-600">Your exam has been successfully submitted.</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-8 p-4 bg-green-50 text-green-700 font-semibold rounded-lg border border-green-200 text-center">
            ✓ {{ session('success') }}
        </div>
    @endif

    <!-- Candidate Information -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Your Details</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                <span class="font-semibold text-gray-700">Exam Name:</span>
                <span class="text-gray-900 font-medium">{{ $summary['exam_name'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                <span class="font-semibold text-gray-700">Name:</span>
                <span class="text-gray-900 font-medium">{{ $summary['student_name'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                <span class="font-semibold text-gray-700">Email:</span>
                <span class="text-gray-900 font-medium">{{ $summary['student_email'] ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                <span class="font-semibold text-gray-700">Submitted At:</span>
                <span class="text-gray-900 font-mono text-sm">{{ $summary['submitted_at'] ?? now()->toDateTimeString() }}</span>
            </div>
        </div>
    </div>

    <!-- What Happens Next -->
    <div class="mb-10 p-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
        <h3 class="text-lg font-bold text-blue-900 mb-2">What Happens Next?</h3>
        <p class="text-blue-800">
            Your exam answers are being reviewed. For multiple-choice questions, your marks will be calculated automatically. 
            For descriptive questions, our team will review your answers and provide marks shortly.
        </p>
    </div>

    <!-- Close Button -->
    <div class="text-center">
        <a href="/" class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg transition duration-200">
            Return to Home
        </a>
    </div>
</div>

<script>
(function () {
    // 🔒 Push multiple states to trap back navigation
    function preventBack() {
        window.history.pushState(null, "", window.location.href);
    }

    // Push state multiple times (stronger effect)
    for (let i = 0; i < 50; i++) {
        preventBack();
    }

    // 🚫 Handle back button
    window.addEventListener("popstate", function () {
        preventBack();
        window.location.replace(window.location.href);
    });

    // 🚫 Prevent caching (important for exams)
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // 🚫 Disable keyboard back shortcuts (extra layer)
    document.addEventListener("keydown", function (e) {
        // ALT + LEFT ARROW
        if (e.altKey && e.key === "ArrowLeft") {
            e.preventDefault();
        }

        // Backspace (outside input fields)
        if (e.key === "Backspace" &&
            !["INPUT", "TEXTAREA"].includes(document.activeElement.tagName)) {
            e.preventDefault();
        }
    });

})();
</script>

</body>
</html>
