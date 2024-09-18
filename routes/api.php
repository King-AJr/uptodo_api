<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/google_login', [SocialLoginController::class, 'handleGoogleSignIn']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/all_tasks', [TaskController::class, 'index']);
    Route::post('/add_tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::post('/update_task/{id}', [TaskController::class, 'update']);
    Route::post('/delete_task/{id}', [TaskController::class, 'destroy']);
    Route::get('/all_categories', [CategoryController::class, 'index']);
    Route::post('/add_categories', [CategoryController::class, 'store']);
});
