<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the 'api' middleware group. Enjoy building your API!
|
*/

Route::pattern('id', '[0-9]+');

Route::withoutMiddleware('auth:service-api-key,api')->group(function () {
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/auth/reset', [AuthController::class, 'resetAction']);
        Route::post('/auth/send-login-details', [AuthController::class, 'sendLoginDetailsAction']);
        Route::get('/auth/mail', [AuthController::class, 'getEmailByResetTokenAction']);
        Route::post('/auth/store', [AuthController::class, 'setNewPasswordAction']);
        Route::put('/v2/auth/complete/signup', [AuthController::class, 'completeUserSignup']);
    });
});
