<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadPhotoController;
use Illuminate\Http\Request;

// Upload Photo Routes
Route::post('/upload_Photo', [UploadPhotoController::class, 'upload_Photo']);
Route::get('/show_Photo', [UploadPhotoController::class, 'show_Photo']);
Route::post('/delete_Photo/{id}', [UploadPhotoController::class, 'delete_Photo']);
Route::post('/search_Specifically', [UploadPhotoController::class, 'search_Specifically']);
// Route::post('/public_Photos', [UploadPhotoController::class, 'public_Photos']);
Route::post('/private_Photos', [UploadPhotoController::class, 'private_Photos']);
Route::post('/photo_Permissions_send', [UploadPhotoController::class, 'photo_Permissions_send']);
Route::post('/photo_Permissions_accept', [UploadPhotoController::class, 'photo_Permissions_accept']);