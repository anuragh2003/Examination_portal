<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Examination Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-indigo-600 min-h-screen flex items-center justify-center">

  <div class="bg-white rounded-3xl shadow-2xl p-10 w-full max-w-md transform transition duration-500 hover:scale-105">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-extrabold text-blue-600 mb-2">Examination Portal</h1>
      <p class="text-gray-500">Admin Login</p>
    </div>

    @if ($errors->any())
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
      @csrf
      <div>
        <label class="block text-gray-700 font-semibold mb-1">Email</label>
        <input type="email" name="email" placeholder="Enter your email" required
          class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-1">Password</label>
        <input type="password" name="password" placeholder="Enter your password" required
          class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">Login</button>
    </form>

    <p class="text-center text-gray-500 mt-6 text-sm">
      Only authorized admin can log in.
    </p>
  </div>

</body>
</html>
