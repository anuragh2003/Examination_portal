<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">

    <!-- Navbar -->
    <header class="bg-blue-600 text-white px-4 md:px-6 py-4 flex justify-between items-center shadow">
        <h1 class="text-lg md:text-xl font-bold">Examination Portal</h1>
        <div class="flex items-center gap-2 md:gap-4">
            <span class="hidden sm:block">Welcome, Admin</span>

            <!-- Logout form (POST) -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="bg-red-600 px-3 py-2 md:px-4 md:py-2 rounded hover:bg-red-700 text-sm md:text-base">
                    Logout
                </button>
            </form>
            
        </div>
    </header>
</body>
</html>
