<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

// 🔐 AUTH
Route::post('/login', [AuthController::class, 'login']);

// 🌍 PUBLIC BLOG
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

// 🔐 ADMIN CMS
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // 🖼️ IMAGE UPLOAD
    Route::post('/upload', [PostController::class, 'upload']);
});