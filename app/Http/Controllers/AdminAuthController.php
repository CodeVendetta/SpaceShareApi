<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $admin->tokens()->delete();

        $token = $admin->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'admin' => $admin
        ], 200);
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     $admin = Admin::where('email', $request->email)->first();

    //     if (!$admin || !Hash::check($request->password, $admin->password)) {
    //         throw ValidationException::withMessages([
    //             'email' => ['email atau password salah.'],
    //         ]);
    //     }

    //     $admin->tokens()->delete();

    //     $token = $admin->createToken('admin_token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login berhasil',
    //         'admin' => $admin,
    //         'token' => $token,
    //     ]);
    // }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
