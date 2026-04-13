<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen p-4 sm:p-8">

<div class="max-w-7xl mx-auto">
    <!-- Back to Dashboard Button -->
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">👥 Students</h1>
        <p class="text-gray-600">View and manage all imported students</p>
    </div>

    <!-- Import Students Button -->
    <div class="mb-6">
        <a href="{{ route('students.import.form') }}"
           class="bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition font-semibold shadow-md">
            📤 Import Students
        </a>
    </div>

    <!-- Filter & Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm font-semibold">Total Students</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalStudents }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
            <p class="text-gray-600 text-sm font-semibold">Completed</p>
            <p class="text-3xl font-bold text-green-600">{{ $completedStudents }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm font-semibold">In Progress</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $inProgressStudents }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm font-semibold">Not Started</p>
            <p class="text-3xl font-bold text-purple-600">{{ $notStartedStudents }}</p>
        </div>
    </div>

    <!-- Filter Toggle Button -->
    <div class="mb-6 flex items-center gap-2">
        <button id="filterToggleBtn" class="flex items-center gap-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            🔍 Advanced Filters
        </button>
        <span id="activeFilterCount" class="text-gray-600 font-semibold text-sm"></span>
    </div>

    <!-- Advanced Filters (Hidden by Default) -->
    <div id="filterPanel" class="mb-6 p-6 bg-white rounded-lg shadow-md hidden">
        <form id="filterForm" method="GET" action="{{ route('students.list') }}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Filter Students</h3>
                <button type="button" id="filterCloseBtn" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Name Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name:</label>
                <input type="text" name="nameFilter" id="nameFilter" placeholder="Search by name..." value="{{ request('nameFilter') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Email Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email:</label>
                <input type="text" name="emailFilter" id="emailFilter" placeholder="Search by email..." value="{{ request('emailFilter') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Role Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role:</label>
                <select id="roleFilter" name="roleFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('roleFilter') == $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
            </div>

            <!-- City Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">City:</label>
                <select id="cityFilter" name="cityFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('cityFilter') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status:</label>
                <select id="statusFilter" name="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('statusFilter') == 'completed' ? 'selected' : '' }}>✓ Completed</option>
                    <option value="in-progress" {{ request('statusFilter') == 'in-progress' ? 'selected' : '' }}>⏳ In Progress</option>
                    <option value="not-started" {{ request('statusFilter') == 'not-started' ? 'selected' : '' }}>📝 Not Started</option>
                </select>
            </div>

            <!-- From Date Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Registered From:</label>
                <input type="date" name="fromDateFilter" id="fromDateFilter" value="{{ request('fromDateFilter') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- To Date Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Registered To:</label>
                <input type="date" name="toDateFilter" id="toDateFilter" value="{{ request('toDateFilter') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                ✅ Apply Filters
            </button>
            <a href="{{ route('students.list') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                🔄 Reset Filters
            </a>
            <span class="ml-auto text-gray-600 font-semibold">
                Showing {{ $students->count() }} of {{ $students->total() }} students
            </span>
        </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-xs">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-xs">Email</th>
                        <th class="px-4 py-3 text-left font-semibold text-xs">Contact</th>
                        <th class="px-4 py-3 text-left font-semibold text-xs">City</th>
                        <th class="px-4 py-3 text-left font-semibold text-xs">Role</th>
                        <th class="px-4 py-3 text-left font-semibold text-xs">Registered</th>
                        <th class="px-4 py-3 text-center font-semibold text-xs">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-xs">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2">
                                <div class="font-medium text-gray-900 truncate max-w-xs">{{ $student->candidate_name }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-gray-900 truncate max-w-xs">{{ $student->candidate_email }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-gray-900 truncate">{{ $student->candidate_contact }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-gray-900 truncate max-w-xs">{{ $student->candidate_city ?: 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-block bg-blue-100 px-2 py-1 rounded text-blue-800 font-medium text-xs truncate max-w-xs">{{ $student->role }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-gray-900 text-xs">{{ $student->registered_at ? \Carbon\Carbon::parse($student->registered_at)->format('M d, Y H:i') : 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($student->attempt_completed)
                                    <span class="status-badge status-completed text-xs">✓ Completed</span>
                                @elseif($student->started_at)
                                    <span class="status-badge status-in-progress text-xs">⏳ In Progress</span>
                                @else
                                    <span class="status-badge status-not-started text-xs">📝 Not Started</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex gap-1 justify-center flex-wrap">
                                    <button onclick="openEditModal(this)"
                                            data-student="{{ json_encode($student) }}"
                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition">
                                        ✏️ Edit
                                    </button>
                                    <button onclick="viewDetails(this)"
                                            data-student="{{ json_encode($student) }}"
                                            class="px-2 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600 transition">
                                        👁️ View
                                    </button>
                                    <button onclick="confirmDelete(this)"
                                            data-student-id="{{ $student->id }}"
                                            class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <p class="text-lg font-medium">No students found</p>
                                <p class="text-sm">Import students to get started</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-white border-t border-gray-200">
            {{ $students->withQueryString()->links() }}
        </div>
    </div>

</div>

<!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8 max-h-96 overflow-y-auto">
        <h2 class="text-2xl font-bold mb-4" id="modalTitle">Student Details</h2>
        <div id="modalContent" class="space-y-3"></div>
        <button onclick="closeModal()" class="mt-6 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            Close
        </button>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Edit Student</h2>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                    <input type="text" id="editName" name="candidate_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" id="editEmail" name="candidate_email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact</label>
                    <input type="text" id="editContact" name="candidate_contact" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                    <input type="text" id="editCity" name="candidate_city"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <input type="text" id="editRole" name="role" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    💾 Save Changes
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-8">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mt-4 text-center">Delete Student</h3>
        <p class="text-gray-600 text-center mt-2">Are you sure you want to delete this student? This action cannot be undone.</p>
        <div class="flex gap-3 mt-6">
            <form id="deleteForm" method="POST" action="" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    Yes, Delete
                </button>
            </form>
            <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
    function viewDetails(button) {
        const studentData = button.getAttribute('data-student');
        const student = JSON.parse(studentData);
        const modal = document.getElementById('detailsModal');
        const content = document.getElementById('modalContent');

        const html = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Name</p>
                    <p class="text-gray-900">${student.candidate_name}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Email</p>
                    <p class="text-gray-900">${student.candidate_email}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Contact</p>
                    <p class="text-gray-900">${student.candidate_contact}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">City</p>
                    <p class="text-gray-900">${student.candidate_city || 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Role</p>
                    <p class="text-gray-900">${student.role}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Status</p>
                    <p class="text-gray-900">
                        ${student.attempt_completed ? '✓ Completed' :
                          student.started_at ? '⏳ In Progress' : '📝 Not Started'}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Registered At</p>
                    <p class="text-gray-900">${student.registered_at ? new Date(student.registered_at).toLocaleString() : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Started At</p>
                    <p class="text-gray-900">${student.started_at ? new Date(student.started_at).toLocaleString() : 'N/A'}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 font-semibold">Submitted At</p>
                    <p class="text-gray-900">${student.submitted_at ? new Date(student.submitted_at).toLocaleString() : 'N/A'}</p>
                </div>
            </div>
        `;

        content.innerHTML = html;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    function openEditModal(button) {
        const studentData = button.getAttribute('data-student');
        const student = JSON.parse(studentData);
        const editForm = document.getElementById('editForm');
        
        // Set form action URL
        editForm.action = `/students/${student.id}`;
        
        // Populate form fields
        document.getElementById('editName').value = student.candidate_name;
        document.getElementById('editEmail').value = student.candidate_email;
        document.getElementById('editContact').value = student.candidate_contact;
        document.getElementById('editCity').value = student.candidate_city || '';
        document.getElementById('editRole').value = student.role;
        
        // Show modal
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function confirmDelete(button) {
        const studentId = button.getAttribute('data-student-id');
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/students/${studentId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Toggle filter panel visibility
    document.getElementById('filterToggleBtn').addEventListener('click', () => {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.classList.toggle('hidden');
    });

    document.getElementById('filterCloseBtn').addEventListener('click', () => {
        document.getElementById('filterPanel').classList.add('hidden');
    });
</script>

<style>
    .status-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
    }
    .status-completed {
        background-color: rgb(220 252 231);
        color: rgb(22 101 52);
    }
    .status-in-progress {
        background-color: rgb(254 243 199);
        color: rgb(146 64 14);
    }
    .status-not-started {
        background-color: rgb(243 244 246);
        color: rgb(31 41 55);
    }
    
    /* Ensure text truncation works properly */
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

</body>
</html>