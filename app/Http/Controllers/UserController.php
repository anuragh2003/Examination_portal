<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    // Login
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    $credentials = $request->only('email', 'password');

    if (! $token = JWTAuth::attempt($credentials)) {
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    $user = JWTAuth::user();

    if ($user->email !== 'admin@email.com') {
        return back()->withErrors(['email' => 'Access denied'])->withInput();
    }

    // Store entire user object in session
    session(['user' => $user]);

    // Redirect to dashboard
    return redirect()->route('dashboard');
}



    // Logout
   public function logout()
{
    session()->flush(); // remove all session data
    return redirect()->route('login');
}


    // Refresh
    

    // Token response
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60, // this works fine
        ]);
    }
}