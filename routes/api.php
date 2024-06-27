<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;


//open routes
Route::post('register', [ApiController::class, 'register'])->name('register');
Route::post('login', [ApiController::class, 'login'])->name('login');
Route::post('test-forgot-password', [ApiController::class,'forgotPassword'])->name('forgot-password');
Route::post('test-reset-password', [ApiController::class,'resetPassword'])->name('reset-password');

//protected routes
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('profile', [ApiController::class, 'profile'])->name('profile');
    Route::get('logout', [ApiController::class, 'logout'])->name('logout');
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
