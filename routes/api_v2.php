<?php

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\AdminExportApplicationController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Auditable\UpdateAuditableStatusController;
use App\Http\Controllers\V2\AuditStatus\DeleteAuditStatusController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringImportController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringProjectController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringSiteController;
use App\Http\Controllers\V2\Dashboard\CountryAndPolygonDataController;
use App\Http\Controllers\V2\Dashboard\ViewProjectController;
use App\Http\Controllers\V2\DisturbanceReports\ExportDisturbanceReportController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Entities\GetAggregateReportsController;
use App\Http\Controllers\V2\Exports\ExportAllMonitoredEntitiesController;
use App\Http\Controllers\V2\Exports\ExportAllNurseryDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllProjectDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllSiteDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportImageController;
use App\Http\Controllers\V2\Exports\ExportProjectEntityAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportReportEntityAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\GeneratePreSignedURLDownloadReportController;
use App\Http\Controllers\V2\Exports\ProjectAdminExportController;
use App\Http\Controllers\V2\Files\FilePropertiesController;
use App\Http\Controllers\V2\Files\Location\NurseryImageLocationsController;
use App\Http\Controllers\V2\Files\Location\NurseryReportImageLocationsController;
use App\Http\Controllers\V2\Files\Location\ProjectImageLocationsController;
use App\Http\Controllers\V2\Files\Location\ProjectReportImageLocationsController;
use App\Http\Controllers\V2\Files\Location\SiteImageLocationsController;
use App\Http\Controllers\V2\Files\Location\SiteReportImageLocationsController;
use App\Http\Controllers\V2\Files\UploadController;
use App\Http\Controllers\V2\FinancialIndicators\UpsertFinancialIndicatorsController;
use App\Http\Controllers\V2\FinancialReports\ExportFinancialReportController;
use App\Http\Controllers\V2\Forms\ExportFormSubmissionController;
use App\Http\Controllers\V2\FundingType\DeleteFundingTypeController;
use App\Http\Controllers\V2\FundingType\StoreFundingTypeController;
use App\Http\Controllers\V2\FundingType\UpdateFundingTypeController;
use App\Http\Controllers\V2\Geometry\GeometryController;
use App\Http\Controllers\V2\Leaderships\DeleteLeadershipsController;
use App\Http\Controllers\V2\Leaderships\StoreLeadershipsController;
use App\Http\Controllers\V2\Leaderships\UpdateLeadershipsController;
use App\Http\Controllers\V2\MediaController;
use App\Http\Controllers\V2\MonitoredData\IndicatorEntitySlugExportController;
use App\Http\Controllers\V2\Nurseries\AdminNurseriesMultiController;
use App\Http\Controllers\V2\Organisations\AdminApproveOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminExportOrganisationsController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationMultiController;
use App\Http\Controllers\V2\Organisations\AdminRejectOrganisationController;
use App\Http\Controllers\V2\Organisations\CreateOrganisationInviteController;
use App\Http\Controllers\V2\Organisations\OrganisationApprovedUsersController;
use App\Http\Controllers\V2\Organisations\OrganisationController;
use App\Http\Controllers\V2\Organisations\OrganisationListRequestedUsersController;
use App\Http\Controllers\V2\OwnershipStake\DeleteOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\StoreOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\UpdateOwnershipStakeController;
use App\Http\Controllers\V2\ProjectPipeline\DeleteProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\GetProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\StoreProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\UpdateProjectPipelineController;
use App\Http\Controllers\V2\ProjectPitches\ExportProjectPitchController;
use App\Http\Controllers\V2\Projects\AdminProjectMultiController;
use App\Http\Controllers\V2\Projects\CreateProjectInviteController;
use App\Http\Controllers\V2\Projects\DeleteProjectMonitoringPartnersController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminCreateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminSoftDeleteProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminUpdateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\ProjectInviteAcceptController;
use App\Http\Controllers\V2\Projects\ProjectManagersController;
use App\Http\Controllers\V2\Projects\ViewAProjectsMonitoringsController;
use App\Http\Controllers\V2\Projects\ViewProjectMonitoringPartnersController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminCreateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminDeleteReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminIndexReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminUpdateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkViaAccessCodeController;
use App\Http\Controllers\V2\Sites\AdminSitesMultiController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminCreateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminSoftDeleteSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminUpdateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\ViewSiteMonitoringController;
use App\Http\Controllers\V2\Sites\ViewASitesMonitoringsController;
use App\Http\Controllers\V2\SrpReports\ExportSrpReportController;
use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use App\Http\Controllers\V2\User\AdminResetPasswordController;
use App\Http\Controllers\V2\User\AdminUserController;
use App\Http\Controllers\V2\User\AdminUserCreationController;
use App\Http\Controllers\V2\User\AdminUserMultiController;
use App\Http\Controllers\V2\User\AdminUsersOrganizationController;
use App\Http\Controllers\V2\User\AdminVerifyUserController;
use App\Http\Controllers\V2\User\CompleteActionController;
use App\Http\Controllers\V2\User\UpdateMyBannersController;
use App\Http\Middleware\ModelInterfaceBindingMiddleware;
use App\Models\V2\AuditableModel;
use App\Models\V2\EntityModel;
use App\Models\V2\MediaModel;
use Illuminate\Http\Request;
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

Route::get('debug/error', function () {
    throw new Exception('Test exception', 500);
});

Route::prefix('project-metrics')->group(function () {
    Route::post('', [BaselineMonitoringProjectController::class, 'create']);
    Route::get('', [BaselineMonitoringProjectController::class, 'index']);
    Route::get('/{uuid}', [BaselineMonitoringProjectController::class, 'view']);

    Route::get('/{uuid}/overview', [BaselineMonitoringProjectController::class, 'overview']);
    Route::get('/{uuid}/download', [BaselineMonitoringProjectController::class, 'download']);
    Route::post('/upload', [BaselineMonitoringProjectController::class, 'upload']);
    Route::put('/{uuid}', [BaselineMonitoringProjectController::class, 'update']);
    Route::delete('/{uuid}', [BaselineMonitoringProjectController::class, 'delete']);
});

Route::prefix('site-metrics')->group(function () {
    Route::post('', [BaselineMonitoringSiteController::class, 'create']);
    Route::get('', [BaselineMonitoringSiteController::class, 'index']);
    Route::get('/{uuid}', [BaselineMonitoringSiteController::class, 'view']);
    Route::put('/{uuid}', [BaselineMonitoringSiteController::class, 'update']);
    Route::delete('/{uuid}', [BaselineMonitoringSiteController::class, 'delete']);
});

Route::prefix('imports')->group(function () {
    Route::post('baseline-monitoring', BaselineMonitoringImportController::class);
});

Route::prefix('media')->group(function () {
    Route::delete('', [MediaController::class, 'bulkDelete']);
    Route::delete('/{uuid}', [MediaController::class, 'delete']);
    Route::delete('/{uuid}/{collection}', [MediaController::class, 'delete']);
});
/** ADMIN ONLY ROUTES */
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::prefix('reporting-frameworks')->group(function () {
        Route::get('', AdminIndexReportingFrameworkController::class);
        Route::post('', AdminCreateReportingFrameworkController::class);
        Route::delete('/{uuid}', AdminDeleteReportingFrameworkController::class);
        Route::put('{framework}', AdminUpdateReportingFrameworkController::class);
    });

    Route::prefix('organisations')->group(function () {
        Route::get('multi', AdminOrganisationMultiController::class);
        Route::put('approve', AdminApproveOrganisationController::class);
        Route::put('reject', AdminRejectOrganisationController::class);
        Route::get('export', AdminExportOrganisationsController::class);
    });
    Route::resource('organisations', AdminOrganisationController::class)->except('create');

    Route::prefix('projects')->group(function () {
        Route::get('/multi', AdminProjectMultiController::class);
    });

    Route::prefix('project-monitorings')->group(function () {
        Route::post('/', AdminCreateProjectMonitoringController::class);
        Route::put('/{projectMonitoring}', AdminUpdateProjectMonitoringController::class);
        Route::delete('/{projectMonitoring}', AdminSoftDeleteProjectMonitoringController::class);
    });

    Route::prefix('sites')->group(function () {
        Route::get('/multi', AdminSitesMultiController::class);
    });

    Route::prefix('site-monitorings')->group(function () {
        Route::post('/', AdminCreateSiteMonitoringController::class);
        Route::put('/{siteMonitoring}', AdminUpdateSiteMonitoringController::class);
        Route::delete('/{siteMonitoring}', AdminSoftDeleteSiteMonitoringController::class);
    });

    Route::prefix('nurseries')->group(function () {
        Route::get('/multi', AdminNurseriesMultiController::class);
    });

    Route::get('/{entity}/export/{framework}', ExportAllMonitoredEntitiesController::class);
    Route::get('/{entity}/presigned-url/{framework}', GeneratePreSignedURLDownloadReportController::class);
    Route::get('/{entity}/export/{framework}/pm', ProjectAdminExportController::class);

    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::post('/{entity}/reminder', AdminSendReminderController::class);
    });

    Route::prefix('users')->group(function () {
        Route::get('multi', AdminUserMultiController::class);
        Route::put('reset-password/{user}', AdminResetPasswordController::class);
        Route::patch('verify/{user}', AdminVerifyUserController::class);
        Route::get('users-organisation-list/{organisation}', AdminUsersOrganizationController::class);
        Route::post('/create', [AdminUserCreationController::class, 'store']);
    });
    Route::resource('users', AdminUserController::class);

    Route::prefix('forms')->group(function () {
        Route::get('submissions/{form}/export', ExportFormSubmissionController::class);
        Route::get('applications/{fundingProgramme}/export', AdminExportApplicationController::class);
    });

    Route::prefix('project-pitches')->group(function () {
        Route::get('/export', ExportProjectPitchController::class);
    });
});

/** NON ADMIN ROUTES */
Route::prefix('organisations')->group(function () {
    Route::get('user-requests/{organisation}', OrganisationListRequestedUsersController::class);
    Route::get('approved-users/{organisation}', OrganisationApprovedUsersController::class);

    Route::post('/{organisation}/invite', CreateOrganisationInviteController::class);
    // Route::post('/invite/accept', ProjectInviteAcceptController::class);
});
Route::resource('organisations', OrganisationController::class);

Route::prefix('my')->group(function () {
    Route::patch('/banners', UpdateMyBannersController::class);

    Route::prefix('actions')->group(function () {
        Route::put('/{action}/complete', CompleteActionController::class);
    });
});

Route::post('/users/resend', [AuthController::class, 'resendByEmail'])->withoutMiddleware('auth:service-api-key,api');

Route::prefix('reporting-frameworks')->group(function () {
    Route::get('/{frameworkKey}', ViewReportingFrameworkController::class);
    Route::get('/access-code/{accessCode}', ViewReportingFrameworkViaAccessCodeController::class);
});

Route::prefix('applications')->group(function () {
    Route::get('/{application}/export', ExportApplicationController::class);
});

Route::get('/{entityType}/{uuid}/aggregate-reports', GetAggregateReportsController::class)
->whereIn('entityType', ['project', 'site']);

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

Route::prefix('financial-indicators')->group(function () {
    Route::patch('/', UpsertFinancialIndicatorsController::class);
});

Route::prefix('financial-reports')->group(function () {
    Route::get('/export', ExportFinancialReportController::class);
});

Route::prefix('disturbance-reports')->group(function () {
    Route::get('/export', ExportDisturbanceReportController::class);
});

Route::prefix('srp-reports')->group(function () {
    Route::get('/export', ExportSrpReportController::class);
});

Route::prefix('projects')->group(function () {
    Route::get('/{project}/partners', ViewProjectMonitoringPartnersController::class);
    Route::get('/{project}/monitorings', ViewAProjectsMonitoringsController::class);
    Route::get('/{project}/image/locations', ProjectImageLocationsController::class);

    Route::post('/{project}/invite', CreateProjectInviteController::class);
    Route::post('/invite/accept', ProjectInviteAcceptController::class);

    Route::resource('/{project}/managers', ProjectManagersController::class)->only(['index', 'store', 'destroy']);

    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);

    Route::delete('/{project}/{email}/remove-partner', DeleteProjectMonitoringPartnersController::class);
});

Route::prefix('project-reports')->group(function () {
    Route::get('/{projectReport}/image/locations', ProjectReportImageLocationsController::class);
});

Route::prefix('sites/{site}')->group(function () {
    Route::get('/monitorings', ViewASitesMonitoringsController::class);
    Route::get('/image/locations', SiteImageLocationsController::class);
    Route::get('/export', ExportAllSiteDataAsProjectDeveloperController::class);
});

Route::prefix('geometry')->group(function () {
    Route::post('', [GeometryController::class, 'storeGeometry']);
    Route::post('/validate', [GeometryController::class, 'validateGeometries']);
    Route::delete('', [GeometryController::class, 'deleteGeometries']);
    Route::put('{polygon}', [GeometryController::class, 'updateGeometry']);
    Route::post('{polygon}/new-version', function ($polygon, Request $request) {
        return CreateVersionPolygonGeometryHelper::createVersionPolygonGeometry($polygon, $request);
    });

});

Route::prefix('site-monitorings')->group(function () {
    Route::get('/{siteMonitoring}', ViewSiteMonitoringController::class);
});

Route::prefix('site-reports')->group(function () {
    Route::get('/{siteReport}/image/locations', SiteReportImageLocationsController::class);
});

Route::prefix('nurseries')->group(function () {
    Route::get('/{nursery}/image/locations', NurseryImageLocationsController::class);
    Route::get('/{nursery}/export', ExportAllNurseryDataAsProjectDeveloperController::class);
});

Route::prefix('nursery-reports')->group(function () {
    Route::get('/{nurseryReport}/image/locations', NurseryReportImageLocationsController::class);
});

Route::get('/{entity}/{uuid}/export', ExportReportEntityAsProjectDeveloperController::class);

Route::prefix('funding-type')->group(function () {
    Route::post('/', StoreFundingTypeController::class);
    Route::patch('/{fundingType}', UpdateFundingTypeController::class);
    Route::delete('/{fundingType}', DeleteFundingTypeController::class);
});

Route::prefix('terrafund')->group(function () {
    Route::post('/polygon/{uuid}', [TerrafundCreateGeometryController::class, 'processGeometry']);
    Route::post('/validation/polygon', [TerrafundCreateGeometryController::class, 'sendRunValidationPolygon']);
});

ModelInterfaceBindingMiddleware::with(
    MediaModel::class,
    function () {
        Route::post('/{collection}/{mediaModel}/bulk_url', [UploadController::class, 'bulkUrlUpload']);
    },
    prefix: 'file/upload',
    modelParameter: 'mediaModel'
);

Route::post('/export-image', ExportImageController::class);

Route::resource('files', FilePropertiesController::class);

ModelInterfaceBindingMiddleware::with(AuditableModel::class, function () {
    Route::put('/{auditable}/status', UpdateAuditableStatusController::class);
    Route::delete('/{auditable}/{uuid}/delete', DeleteAuditStatusController::class);
});

Route::prefix('dashboard')->withoutMiddleware('auth:service-api-key,api')->group(function () {
    Route::get('/polygon-data/{uuid}', [CountryAndPolygonDataController::class, 'getPolygonData']);
    Route::get('/view-project/{uuid}', [ViewProjectController::class, 'getIfUserIsAllowedToProject']);
});

Route::prefix('indicators')->group(function () {
    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::get('/{entity}/{slug}/export', IndicatorEntitySlugExportController::class);
    });
});

Route::prefix('project-pipeline')->group(function () {
    Route::get('/', GetProjectPipelineController::class);
    Route::get('/{id}', GetProjectPipelineController::class);
    Route::post('/', StoreProjectPipelineController::class);
    Route::put('/{id}', UpdateProjectPipelineController::class);
    Route::delete('/{id}', DeleteProjectPipelineController::class);
});
