<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            // Wajib untuk keamanan SPA (mencegah Session Fixation)
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login Berhasil',
                'user' => Auth::user()
            ], 200);
        }

        return response()->json([
            'message' => 'Email atau Password salah.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout Berhasil'], 200);
    }
}