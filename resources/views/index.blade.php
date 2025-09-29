@include('header')

<main class="flex-grow p-8">
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
    <div id="success-message" 
         class="bg-green-100 text-green-800 p-3 rounded mb-4 shadow-sm transition-opacity duration-500">
      {{ session('success') }}
    </div>

    <script>
      setTimeout(() => {
        const msg = document.getElementById('success-message');
        if (msg) {
          msg.classList.add('opacity-0'); // fade out
          setTimeout(() => msg.remove(), 500); // remove after fade
        }
      }, 1000);
    </script>
  @endif

  <!-- Exams Grid -->
  @if($exams->isEmpty())
    <p class="text-gray-600 mt-4">No exams created yet.</p>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
  @foreach($exams as $exam)
    <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 relative">
  <!-- Whole card clickable (link) -->
  <a href="{{ route('exams.show', $exam->uuid) }}" class="block">
    <h3 class="font-bold text-lg mb-2 truncate">{{ $exam->name }}</h3>
    <p class="text-gray-500 mb-2">
      Marks: {{ $exam->total_marks }} | Duration: {{ $exam->duration_minutes }} min
    </p>
    <span class="px-3 py-1 text-sm font-semibold rounded-full 
      {{ $exam->status == 'active' ? 'bg-green-200 text-green-800' : ($exam->status == 'draft' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-800') }}">
      {{ ucfirst($exam->status) }}
    </span>
  </a>

  <!-- Delete button (top-right corner) -->
  <form action="{{ route('exams.destroy', $exam->uuid) }}" method="POST" 
        onsubmit="return confirm('Are you sure you want to delete this exam?')" 
        class="absolute top-3 right-3">
    @csrf
    @method('DELETE')
    <button type="submit" 
      class="bg-red-600 text-white px-3 py-1 text-sm rounded hover:bg-red-700 transition">
      Delete
    </button>
  </form>
</div>


    </div>
  @endforeach
</div>

  @endif
</main>

@include('footer')
