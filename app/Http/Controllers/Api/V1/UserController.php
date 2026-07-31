<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Mengambil semua data pengguna
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => User::orderBy('created_at', 'desc')->get()
        ]);
    }

    // Menambah pengguna baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // Enkripsi password sebelum disimpan
        $validated['password'] = Hash::make($validated['password']);
        
        // Tetapkan location_id default (misal: 1) jika aplikasi Anda menggunakan lokasi
        $validated['location_id'] = 1; 

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => $user
        ], 201);
    }
}