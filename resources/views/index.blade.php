<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Examination Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-gray-100 to-gray-200 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-blue-600 text-white p-4 flex justify-between items-center shadow-md">
    <h1 class="text-2xl font-bold tracking-wide">Examination Portal</h1>
    <div class="flex items-center">
     <span class="mr-4">Welcome, {{ $user->name ?? 'Admin' }}</span>
      <form class="inline" action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="bg-red-600 px-4 py-2 rounded hover:bg-red-700 transition">
          Logout
        </button>
      </form>
    </div>
  </nav>

  <main class="p-8">
    <!-- Header + Create Exam -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-semibold mb-1">Dashboard</h2>
        <p class="text-gray-600">Manage your exams quickly and efficiently.</p>
      </div>
      <a href="{{ route('exams.create') }}" 
         class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 transition font-medium">
        + Create Exam
      </a>
    </div>

    <!-- Success message -->
    @if(session('success'))
      <div class="bg-green-100 text-green-800 p-3 rounded mb-4 shadow-sm">
        {{ session('success') }}
      </div>
    @endif

    <!-- Exams Grid -->
    @if($exams->isEmpty())
      <p class="text-gray-600 mt-4">No exams created yet.</p>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($exams as $exam)
          <a href="{{ route('exams.show', $exam->uuid) }}" 
             class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
            <h3 class="font-bold text-lg mb-2 truncate">{{ $exam->name }}</h3>
            <p class="text-gray-500 mb-2">Marks: {{ $exam->total_marks }} | Duration: {{ $exam->duration_minutes }} min</p>
            <span class="px-3 py-1 text-sm font-semibold rounded-full 
              {{ $exam->status == 'active' ? 'bg-green-200 text-green-800' : ($exam->status == 'draft' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-800') }}">
              {{ ucfirst($exam->status) }}
            </span>
          </a>
        @endforeach
      </div>
    @endif
  </main>
</body>
</html>
