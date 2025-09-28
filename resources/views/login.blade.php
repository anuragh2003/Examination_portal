<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Examination Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-blue-600">Login</h2>
    <p class="text-gray-600 text-center mb-6">Welcome back! Please login to your account.</p>

    <form action="#" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Email</label>
        <input type="email" placeholder="Enter your email" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Password</label>
        <input type="password" placeholder="Enter your password" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">Login</button>
    </form>

    <p class="text-sm text-center text-gray-600 mt-6">
      Don’t have an account? 
      <a href="signup.html" class="text-blue-600 font-medium hover:underline">Sign Up</a>
    </p>
  </div>

</body>
</html>
