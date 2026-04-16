<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\AdminExportApplicationController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\DisturbanceReports\ExportDisturbanceReportController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Exports\ExportAllNurseryDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllProjectDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllSiteDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportImageController;
use App\Http\Controllers\V2\Exports\ExportProjectEntityAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportReportEntityAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\GeneratePreSignedURLDownloadReportController;
use App\Http\Controllers\V2\Exports\ProjectAdminExportController;
use App\Http\Controllers\V2\FinancialIndicators\UpsertFinancialIndicatorsController;
use App\Http\Controllers\V2\Leaderships\DeleteLeadershipsController;
use App\Http\Controllers\V2\Leaderships\StoreLeadershipsController;
use App\Http\Controllers\V2\Leaderships\UpdateLeadershipsController;
use App\Http\Controllers\V2\OwnershipStake\DeleteOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\StoreOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\UpdateOwnershipStakeController;
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
    Route::get('/{entity}/presigned-url/{framework}', GeneratePreSignedURLDownloadReportController::class);
    Route::get('/{entity}/export/{framework}/pm', ProjectAdminExportController::class);

    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::post('/{entity}/reminder', AdminSendReminderController::class);
    });

    Route::prefix('forms')->group(function () {
        Route::get('applications/{fundingProgramme}/export', AdminExportApplicationController::class);
    });
});

/** NON ADMIN ROUTES */
Route::post('/users/resend', [AuthController::class, 'resendByEmail'])->withoutMiddleware('auth:service-api-key,api');

Route::prefix('ownership-stake')->group(function () {
    Route::post('/', StoreOwnershipStakeController::class);
    Route::patch('/{ownershipStake}', UpdateOwnershipStakeController::class);
    Route::delete('/{ownershipStake}', DeleteOwnershipStakeController::class);
});

Route::prefix('leaderships')->group(function () {
    Route::post('/', StoreLeadershipsController::class);
    Route::delete('/{leaderships}', DeleteLeadershipsController::class);
    Route::patch('/{leaderships}', UpdateLeadershipsController::class);
});

Route::patch('financial-indicators', UpsertFinancialIndicatorsController::class);

Route::prefix('projects')->group(function () {
    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);
});

Route::get('applications/{application}/export', ExportApplicationController::class);
Route::get('disturbance-reports/export', ExportDisturbanceReportController::class);
Route::get('sites/{site}/export', ExportAllSiteDataAsProjectDeveloperController::class);
Route::get('nurseries/{nursery}/export', ExportAllNurseryDataAsProjectDeveloperController::class);
Route::get('/{entity}/{uuid}/export', ExportReportEntityAsProjectDeveloperController::class);

Route::post('/export-image', ExportImageController::class);
