<?php

use Illuminate\Support\Facades\Route;

// Ha nincs webes frontend, adj vissza JSON-t
Route::get('/', function () {
    return response()->json([
        'message' => 'API is running',
        'status' => 'ok',
        'endpoints' => [
            'login' => '/api/login',
            'posts' => '/api/posts'
        ]
    ]);
});