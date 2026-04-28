<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Middleware\ModelInterfaceBindingMiddleware;
use App\Models\V2\EntityModel;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V2 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API V2 routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the 'api' middleware group. Enjoy building your API!
|
*/

/** ADMIN ONLY ROUTES */
Route::prefix('admin')->middleware(['admin'])->group(function () {
    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::post('/{entity}/reminder', AdminSendReminderController::class);
    });
});

/** NON ADMIN ROUTES */
Route::post('/users/resend', [AuthController::class, 'resendByEmail'])->withoutMiddleware('auth:service-api-key,api');

Route::get('applications/{application}/export', ExportApplicationController::class);
