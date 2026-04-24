<?php

use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Exports\ExportAllNurseryDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllProjectDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllSiteDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportProjectEntityAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportReportEntityAsProjectDeveloperController;
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

Route::prefix('projects')->group(function () {
    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);
});

Route::get('applications/{application}/export', ExportApplicationController::class);
Route::get('sites/{site}/export', ExportAllSiteDataAsProjectDeveloperController::class);
Route::get('nurseries/{nursery}/export', ExportAllNurseryDataAsProjectDeveloperController::class);
Route::get('/{entity}/{uuid}/export', ExportReportEntityAsProjectDeveloperController::class);
