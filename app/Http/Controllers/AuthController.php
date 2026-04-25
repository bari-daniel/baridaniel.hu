<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // Debug: mi jön be, és mi van az adatbázisban?
        if (!$user)
            return response()->json(['message' => 'User not found'], 401);

        // Kézi ellenőrzés logolása
        $isMatch = Hash::check($request->password, $user->password);

        if (!$isMatch) {
            \Log::error('Jelszó nem egyezik. Beírt: ' . $request->password . ' | Hash a DB-ben: ' . $user->password);
            return response()->json(['message' => 'Invalid credentials (password mismatch)'], 401);
        }

        return response()->json(['token' => $user->createToken('admin-token')->plainTextToken]);
    }
}