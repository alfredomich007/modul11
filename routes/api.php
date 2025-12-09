<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;

// Route Autentikasi (Publik - Tidak memerlukan token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route yang memerlukan autentikasi (Protected)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth routes (protected)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Posts routes (protected)
    // Catatan: Jika Anda ingin 'index' dan 'show' tetap publik, pindahkan ke luar group middleware
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});