<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            Log::info('Login attempt started', $request->all());
            
            // Validáció
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            
            Log::info('Validation passed', $validated);
            
            // Felhasználó keresése
            $user = User::where('email', $validated['email'])->first();
            
            if (!$user) {
                Log::warning('User not found', ['email' => $validated['email']]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            
            // Jelszó ellenőrzése
            if (!Hash::check($validated['password'], $user->password)) {
                Log::warning('Invalid password', ['email' => $validated['email']]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            
            // Token generálása
            $token = $user->createToken('admin-token')->plainTextToken;
            
            Log::info('Login successful', ['user_id' => $user->id]);
            
            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
    }
}