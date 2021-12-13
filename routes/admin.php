<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordResetController;

// Admin Routes
Route::post('/register', [AdminController::class, 'register_User']);
Route::get('EmailConfirmation/{email}/{hash}', [AdminController::class, 'verify_Email']);
// Route::post('/login', [AdminController::class, 'login_User']);
Route::middleware(['loginChecks'])->group(function () {
	Route::post('/login', [AdminController::class, 'login_User']);

});
Route::middleware(['admin'])->group(function () {
	Route::post('/logout_User', [AdminController::class, 'logout_User']);
	Route::post('/profile_Update/{id}', [AdminController::class, 'profile_Update']);
	Route::post('/update_password', [AdminController::class, 'update_password']);
});

Route::post('/password_Reset_Link', [PasswordResetController::class, 'password_Reset_Link']);
Route::post('/password_Reset_Process/{email}/{jwt_token}', [PasswordResetController::class, 'password_Reset_Process']);