<?php

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
    });
});
