<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Examination Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen">

  <!-- Navbar -->
  <nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto flex justify-between items-center p-4">
      <h1 class="text-xl font-bold">Examination Portal</h1>
      <div class="space-x-4">
        <a href="/login" class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-gray-200">Login</a>
        <a href="/signin" class="px-4 py-2 bg-yellow-400 text-gray-800 rounded-lg hover:bg-yellow-500">Sign Up</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="flex-grow">
    <section class="text-center py-20 bg-gray-100">
      <h2 class="text-4xl font-bold text-gray-800">Welcome to the Examination Portal</h2>
      <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
        Manage your online exams with ease. Students can register, attend exams, and view results seamlessly.
      </p>
      <div class="mt-6">
        <a href="#" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">Get Started</a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-blue-600 text-white py-4">
    <div class="container mx-auto text-center">
      <p>&copy; 2025 Examination Portal. All Rights Reserved.</p>
    </div>
  </footer>

</body>
</html>
