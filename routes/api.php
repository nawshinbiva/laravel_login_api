<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;


//open routes
Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);

//protected routes
Route::group(['middleware' => ['auth:API']], function () {
    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('logout', [ApiController::class, 'logout']);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
