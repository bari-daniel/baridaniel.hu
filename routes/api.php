<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

// 🔐 AUTH
Route::post('/login', [AuthController::class, 'login']);

// 🌍 PUBLIC BLOG (Csak a látogatóknak)
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

// 🔐 ADMIN CMS (Bejelentkezett felhasználóknak)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/posts', [PostController::class, 'adminIndex']);
    Route::post('/admin/posts', [PostController::class, 'store']);
    Route::put('/admin/posts/{post}', [PostController::class, 'update']);
    Route::delete('/admin/posts/{post}', [PostController::class, 'destroy']);

    // 🖼️ IMAGE UPLOAD
    Route::post('/admin/upload', [PostController::class, 'upload']);
});