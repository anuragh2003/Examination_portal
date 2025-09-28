<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Examination Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-blue-600">Create Account</h2>
    <p class="text-gray-600 text-center mb-6">Join the Examination Portal today!</p>

    <form action="#" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 mb-1">Full Name</label>
        <input type="text" placeholder="Enter your name" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Email</label>
        <input type="email" placeholder="Enter your email" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Password</label>
        <input type="password" placeholder="Create a password" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <div>
        <label class="block text-gray-700 mb-1">Confirm Password</label>
        <input type="password" placeholder="Re-enter password" required 
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
      </div>

      <button type="submit" class="w-full bg-yellow-400 text-gray-800 py-2 rounded-lg hover:bg-yellow-500">Sign Up</button>
    </form>

    <p class="text-sm text-center text-gray-600 mt-6">
      Already have an account? 
      <a href="login.html" class="text-blue-600 font-medium hover:underline">Login</a>
    </p>
  </div>

</body>
</html>
