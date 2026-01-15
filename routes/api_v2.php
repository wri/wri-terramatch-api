<?php

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\AdminDeleteApplicationController;
use App\Http\Controllers\V2\Applications\AdminExportApplicationController;
use App\Http\Controllers\V2\Applications\AdminIndexApplicationController;
use App\Http\Controllers\V2\Applications\AdminViewApplicationController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Applications\ViewApplicationController;
use App\Http\Controllers\V2\Applications\ViewMyApplicationController;
use App\Http\Controllers\V2\Auditable\UpdateAuditableStatusController;
use App\Http\Controllers\V2\Audits\AdminIndexAuditsController;
use App\Http\Controllers\V2\AuditStatus\DeleteAuditStatusController;
use App\Http\Controllers\V2\AuditStatus\GetAuditStatusController;
use App\Http\Controllers\V2\AuditStatus\StoreAuditStatusController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringImportController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringProjectController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringSiteController;
use App\Http\Controllers\V2\Dashboard\CountryAndPolygonDataController;
use App\Http\Controllers\V2\Dashboard\ViewProjectController;
use App\Http\Controllers\V2\DisturbanceReports\DisturbanceReportsController;
use App\Http\Controllers\V2\DisturbanceReports\ExportDisturbanceReportController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Entities\EntityTypeController;
use App\Http\Controllers\V2\Entities\GetAggregateReportsController;
use App\Http\Controllers\V2\Entities\SubmitEntityWithFormController;
use App\Http\Controllers\V2\Entities\UpdateEntityWithFormController;
use App\Http\Controllers\V2\Entities\ViewEntityController;
use App\Http\Controllers\V2\Entities\ViewEntityWithFormController;
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
use App\Http\Controllers\V2\FinancialReports\FinancialReportsController;
use App\Http\Controllers\V2\Forms\DeleteFormSubmissionController;
use App\Http\Controllers\V2\Forms\ExportFormSubmissionController;
use App\Http\Controllers\V2\Forms\FormSubmissionNextStageController;
use App\Http\Controllers\V2\Forms\StoreFormSubmissionController;
use App\Http\Controllers\V2\Forms\SubmitFormSubmissionController;
use App\Http\Controllers\V2\Forms\UpdateFormSubmissionController;
use App\Http\Controllers\V2\Forms\UpdateFormSubmissionStatusController;
use App\Http\Controllers\V2\Forms\ViewFormSubmissionController;
use App\Http\Controllers\V2\FundingProgramme\AdminFundingProgrammeController;
use App\Http\Controllers\V2\FundingProgramme\FundingProgrammeController;
use App\Http\Controllers\V2\FundingProgramme\UpdateFundingProgrammeStatusController;
use App\Http\Controllers\V2\FundingType\DeleteFundingTypeController;
use App\Http\Controllers\V2\FundingType\StoreFundingTypeController;
use App\Http\Controllers\V2\FundingType\UpdateFundingTypeController;
use App\Http\Controllers\V2\Geometry\GeometryController;
use App\Http\Controllers\V2\ImpactStory\ImpactStoryController;
use App\Http\Controllers\V2\Leaderships\DeleteLeadershipsController;
use App\Http\Controllers\V2\Leaderships\StoreLeadershipsController;
use App\Http\Controllers\V2\Leaderships\UpdateLeadershipsController;
use App\Http\Controllers\V2\MediaController;
use App\Http\Controllers\V2\MonitoredData\GetIndicatorPolygonStatusController;
use App\Http\Controllers\V2\MonitoredData\GetPolygonsIndicatorAnalysisController;
use App\Http\Controllers\V2\MonitoredData\GetPolygonsIndicatorAnalysisVerifyController;
use App\Http\Controllers\V2\MonitoredData\IndicatorEntitySlugExportController;
use App\Http\Controllers\V2\MonitoredData\RunIndicatorAnalysisController;
use App\Http\Controllers\V2\Nurseries\AdminNurseriesMultiController;
use App\Http\Controllers\V2\Nurseries\CreateNurseryWithFormController;
use App\Http\Controllers\V2\NurseryReports\NurseryReportsViaNurseryController;
use App\Http\Controllers\V2\Organisations\AdminApproveOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminExportOrganisationsController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationMultiController;
use App\Http\Controllers\V2\Organisations\AdminRejectOrganisationController;
use App\Http\Controllers\V2\Organisations\CreateOrganisationInviteController;
use App\Http\Controllers\V2\Organisations\JoinExistingOrganisationController;
use App\Http\Controllers\V2\Organisations\OrganisationApprovedUsersController;
use App\Http\Controllers\V2\Organisations\OrganisationApproveUserController;
use App\Http\Controllers\V2\Organisations\OrganisationController;
use App\Http\Controllers\V2\Organisations\OrganisationListingController;
use App\Http\Controllers\V2\Organisations\OrganisationListRequestedUsersController;
use App\Http\Controllers\V2\Organisations\OrganisationRejectUserController;
use App\Http\Controllers\V2\Organisations\OrganisationRetractMyDraftController;
use App\Http\Controllers\V2\Organisations\OrganisationSubmitController;
use App\Http\Controllers\V2\OwnershipStake\DeleteOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\StoreOwnershipStakeController;
use App\Http\Controllers\V2\OwnershipStake\UpdateOwnershipStakeController;
use App\Http\Controllers\V2\Polygons\ChangeStatusPolygonsController;
use App\Http\Controllers\V2\Polygons\ViewAllSitesPolygonsForProjectController;
use App\Http\Controllers\V2\Polygons\ViewSitesPolygonsForProjectController;
use App\Http\Controllers\V2\ProjectPipeline\DeleteProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\GetProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\StoreProjectPipelineController;
use App\Http\Controllers\V2\ProjectPipeline\UpdateProjectPipelineController;
use App\Http\Controllers\V2\ProjectPitches\DeleteProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\ExportProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\StoreProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\SubmitProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\UpdateProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\ViewProjectPitchSubmissionsController;
use App\Http\Controllers\V2\ProjectReports\ProjectReportsViaProjectController;
use App\Http\Controllers\V2\Projects\AdminProjectMultiController;
use App\Http\Controllers\V2\Projects\CreateBlankProjectWithFormController;
use App\Http\Controllers\V2\Projects\CreateProjectInviteController;
use App\Http\Controllers\V2\Projects\CreateProjectWithFormController;
use App\Http\Controllers\V2\Projects\DeleteProjectMonitoringPartnersController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminCreateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminSoftDeleteProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminUpdateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\ProjectInviteAcceptController;
use App\Http\Controllers\V2\Projects\ProjectManagersController;
use App\Http\Controllers\V2\Projects\ViewProjectMonitoringPartnersController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminCreateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminDeleteReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminIndexReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminUpdateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkViaAccessCodeController;
use App\Http\Controllers\V2\Reports\NothingToReportReportController;
use App\Http\Controllers\V2\SiteReports\SiteReportsViaSiteController;
use App\Http\Controllers\V2\Sites\AdminSitesMultiController;
use App\Http\Controllers\V2\Sites\CreateSiteWithFormController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminCreateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminSoftDeleteSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminUpdateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\ShowSitePolygonController;
use App\Http\Controllers\V2\Sites\SitePolygonDataController;
use App\Http\Controllers\V2\Sites\StoreSitePolygonNewVersionController;
use App\Http\Controllers\V2\Sites\UpdateSitePolygonActiveController;
use App\Http\Controllers\V2\SrpReports\ExportSrpReportController;
use App\Http\Controllers\V2\SrpReports\SrpReportsController;
use App\Http\Controllers\V2\Stages\DeleteStageController;
use App\Http\Controllers\V2\Stages\IndexStageController;
use App\Http\Controllers\V2\Stages\StoreStageController;
use App\Http\Controllers\V2\Stages\UpdateStageController;
use App\Http\Controllers\V2\Stages\UpdateStageStatusController;
use App\Http\Controllers\V2\Stages\ViewStageController;
use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use App\Http\Controllers\V2\UpdateRequests\AdminIndexUpdateRequestsController;
use App\Http\Controllers\V2\UpdateRequests\AdminSoftDeleteUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminStatusUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminViewUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\EntityUpdateRequestsController;
use App\Http\Controllers\V2\User\AdminResetPasswordController;
use App\Http\Controllers\V2\User\AdminUserController;
use App\Http\Controllers\V2\User\AdminUserCreationController;
use App\Http\Controllers\V2\User\AdminUserMultiController;
use App\Http\Controllers\V2\User\AdminUsersOrganizationController;
use App\Http\Controllers\V2\User\AdminVerifyUserController;
use App\Http\Controllers\V2\User\CompleteActionController;
use App\Http\Controllers\V2\User\IndexMyActionsController;
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
Route::get('impact-stories', [ImpactStoryController::class, 'index'])
    ->withoutMiddleware(['auth:service-api-key,api']);

Route::get('impact-stories/{impact_story}', [ImpactStoryController::class, 'show'])
    ->withoutMiddleware(['auth:service-api-key,api']);

Route::resource('impact-stories', ImpactStoryController::class)
    ->except(['index', 'show']);
/** ADMIN ONLY ROUTES */
Route::prefix('admin')->middleware(['admin'])->group(function () {
    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::get('{entity}', AdminIndexAuditsController::class);
    }, prefix: 'audits');

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

    Route::prefix('update-requests')->group(function () {
        Route::get('', AdminIndexUpdateRequestsController::class);
        Route::delete('/{updateRequest}', AdminSoftDeleteUpdateRequestController::class);
        Route::put('/{updateRequest}/{status}', AdminStatusUpdateRequestController::class);
    });

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


    Route::prefix('funding-programme/stage')->group(function () {
        Route::post('/', StoreStageController::class);
        Route::patch('/{stage}', UpdateStageController::class);
        Route::delete('/{stage}', DeleteStageController::class);
        Route::patch('/{stage}/status', UpdateStageStatusController::class);
    });
    Route::resource('funding-programme', AdminFundingProgrammeController::class)->except('create', 'edit');
    Route::patch('funding-programme/{fundingProgramme}/status', UpdateFundingProgrammeStatusController::class);

    Route::prefix('users')->group(function () {
        Route::get('multi', AdminUserMultiController::class);
        Route::put('reset-password/{user}', AdminResetPasswordController::class);
        Route::patch('verify/{user}', AdminVerifyUserController::class);
        Route::get('users-organisation-list/{organisation}', AdminUsersOrganizationController::class);
        Route::post('/create', [AdminUserCreationController::class, 'store']);
    });
    Route::resource('users', AdminUserController::class);
    Route::post('impact-stories/bulk-delete', [ImpactStoryController::class, 'bulkDestroy']);
    Route::resource('impact-stories', ImpactStoryController::class);

    Route::prefix('forms')->group(function () {
        Route::prefix('submissions')->group(function () {
            Route::get('/{form}/export', ExportFormSubmissionController::class);
            Route::prefix('{formSubmission}')->group(function () {
                Route::patch('/status', UpdateFormSubmissionStatusController::class);
            });
        });

        Route::prefix('applications')->group(function () {
            Route::get('/', AdminIndexApplicationController::class);
            Route::get('/{application}', AdminViewApplicationController::class)->middleware('i18n');
            Route::delete('/{application}', AdminDeleteApplicationController::class);
            Route::get('/{fundingProgramme}/export', AdminExportApplicationController::class);
        });
    });

    Route::prefix('project-pitches')->group(function () {
        Route::get('/export', ExportProjectPitchController::class);
    });
});

/** NON ADMIN ROUTES */
Route::prefix('organisations')->group(function () {
    Route::get('listing', OrganisationListingController::class);
    Route::post('join-existing', JoinExistingOrganisationController::class);
    Route::put('approve-user', OrganisationApproveUserController::class);
    Route::put('reject-user', OrganisationRejectUserController::class);
    Route::put('submit/{organisation}', OrganisationSubmitController::class);
    Route::get('user-requests/{organisation}', OrganisationListRequestedUsersController::class);
    Route::get('approved-users/{organisation}', OrganisationApprovedUsersController::class);
    Route::delete('retract-my-draft', OrganisationRetractMyDraftController::class);

    Route::post('/{organisation}/invite', CreateOrganisationInviteController::class);
    // Route::post('/invite/accept', ProjectInviteAcceptController::class);
});
Route::resource('organisations', OrganisationController::class);

Route::prefix('my')->group(function () {
    Route::patch('/banners', UpdateMyBannersController::class);

    Route::prefix('actions')->group(function () {
        Route::get('/', IndexMyActionsController::class);
        Route::put('/{action}/complete', CompleteActionController::class);
    });
});

Route::post('/users/resend', [AuthController::class, 'resendByEmail'])->withoutMiddleware('auth:service-api-key,api');

Route::prefix('forms')->group(function () {
    Route::prefix('submissions')->group(function () {
        Route::post('/', StoreFormSubmissionController::class);
        Route::patch('/{formSubmission}', UpdateFormSubmissionController::class);
        Route::get('/{formSubmission}', ViewFormSubmissionController::class)->middleware('i18n');
        Route::put('/submit/{formSubmission}', SubmitFormSubmissionController::class)->middleware('i18n');
        Route::post('/{formSubmission}/next-stage', FormSubmissionNextStageController::class);
        Route::delete('/{formSubmission}', DeleteFormSubmissionController::class);
    });

    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::get('/{entity}', ViewEntityWithFormController::class)->middleware('i18n');
        Route::put('/{entity}', UpdateEntityWithFormController::class);
        Route::put('/{entity}/submit', SubmitEntityWithFormController::class);
    });

    Route::prefix('projects')->group(function () {
        Route::post('', CreateProjectWithFormController::class);
        Route::post('/{form}', CreateBlankProjectWithFormController::class);
    });

    Route::prefix('sites')->group(function () {
        Route::post('', CreateSiteWithFormController::class);
    });

    Route::prefix('nurseries')->group(function () {
        Route::post('', CreateNurseryWithFormController::class);
    });
});


Route::prefix('reporting-frameworks')->group(function () {
    Route::get('/{frameworkKey}', ViewReportingFrameworkController::class);
    Route::get('/access-code/{accessCode}', ViewReportingFrameworkViaAccessCodeController::class);
});

Route::get('/my/applications', ViewMyApplicationController::class);
Route::prefix('applications')->group(function () {
    Route::get('/{application}', ViewApplicationController::class)->middleware('i18n');
    Route::get('/{application}/export', ExportApplicationController::class);
});

Route::prefix('funding-programme/stage')->group(function () {
    Route::get('/', IndexStageController::class);
    Route::get('/{stage}', ViewStageController::class);
});

Route::prefix('project-pitches')->group(function () {
    Route::post('/', StoreProjectPitchController::class);
    Route::patch('/{projectPitch}', UpdateProjectPitchController::class);
    Route::delete('/{projectPitch}', DeleteProjectPitchController::class);
    Route::get('/{projectPitch}/submissions', ViewProjectPitchSubmissionsController::class);
    Route::put('/submit/{projectPitch}', SubmitProjectPitchController::class);
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
Route::resource('financial-reports', FinancialReportsController::class)->except('create');

Route::prefix('disturbance-reports')->group(function () {
    Route::get('/export', ExportDisturbanceReportController::class);
});

Route::resource('disturbance-reports', DisturbanceReportsController::class)->except('create');

Route::prefix('srp-reports')->group(function () {
    Route::get('/export', ExportSrpReportController::class);
});

Route::resource('srp-reports', SrpReportsController::class)->except('create');

    Route::prefix('projects')->group(function () {
        Route::get('/{project}/partners', ViewProjectMonitoringPartnersController::class);
        Route::get('/{project}/site-polygons', ViewSitesPolygonsForProjectController::class);
        Route::get('/{project}/site-polygons/all', ViewAllSitesPolygonsForProjectController::class);
    Route::get('/{project}/reports', ProjectReportsViaProjectController::class);
    Route::get('/{project}/image/locations', ProjectImageLocationsController::class);

    Route::post('/{project}/invite', CreateProjectInviteController::class);
    Route::post('/invite/accept', ProjectInviteAcceptController::class);

    Route::resource('/{project}/managers', ProjectManagersController::class)->only(['index', 'store', 'destroy']);

    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);

    Route::delete('/{project}/{email}/remove-partner', DeleteProjectMonitoringPartnersController::class);
});

ModelInterfaceBindingMiddleware::forSlugs(['site-reports', 'nursery-reports'], function () {
    Route::put('/{report}/nothing-to-report', NothingToReportReportController::class);
});

ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
    // Note: projects read is no longer used in v2.
    Route::get('/{entity}', ViewEntityController::class);
});

Route::prefix('project-reports')->group(function () {
    Route::get('/{projectReport}/image/locations', ProjectReportImageLocationsController::class);
});

Route::prefix('sites/{site}')->group(function () {
    Route::get('/reports', SiteReportsViaSiteController::class);
    Route::get('/image/locations', SiteImageLocationsController::class);
    Route::get('/export', ExportAllSiteDataAsProjectDeveloperController::class);
    Route::get('/polygon', [SitePolygonDataController::class, 'getSitePolygonData']);
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

Route::prefix('site-reports')->group(function () {
    Route::get('/{siteReport}/image/locations', SiteReportImageLocationsController::class);
});

Route::prefix('nurseries')->group(function () {
    Route::get('/{nursery}/reports', NurseryReportsViaNurseryController::class);
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

Route::prefix('update-requests')->group(function () {
    Route::get('/{updateRequest}', AdminViewUpdateRequestController::class);
    Route::delete('/{updateRequest}', AdminSoftDeleteUpdateRequestController::class);

    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::get('/{entity}', EntityUpdateRequestsController::class);
    });
});

Route::prefix('terrafund')->group(function () {
    Route::post('/polygon/{uuid}', [TerrafundCreateGeometryController::class, 'processGeometry']);
    Route::post('/validation/polygon', [TerrafundCreateGeometryController::class, 'sendRunValidationPolygon']);
});

Route::get('/funding-programme', [FundingProgrammeController::class, 'index'])->middleware('i18n');
Route::get('/funding-programme/{fundingProgramme}', [FundingProgrammeController::class, 'show']);

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
    Route::post('/{auditable}', StoreAuditStatusController::class);
    Route::get('/{auditable}', GetAuditStatusController::class);
}, prefix: 'audit-status');

ModelInterfaceBindingMiddleware::with(AuditableModel::class, function () {
    Route::put('/{auditable}/status', UpdateAuditableStatusController::class);
    Route::delete('/{auditable}/{uuid}/delete', DeleteAuditStatusController::class);
});

Route::prefix('dashboard')->withoutMiddleware('auth:service-api-key,api')->group(function () {
    Route::get('/polygon-data/{uuid}', [CountryAndPolygonDataController::class, 'getPolygonData']);
    Route::get('/view-project/{uuid}', [ViewProjectController::class, 'getIfUserIsAllowedToProject']);
    Route::get('/frameworks', [ViewProjectController::class, 'getFrameworks']);
});

Route::prefix('indicators')->group(function () {
    Route::post('/{slug}', RunIndicatorAnalysisController::class);
    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::get('/{entity}/{slug}', GetPolygonsIndicatorAnalysisController::class);
        Route::get('/{entity}/{slug}/verify', GetPolygonsIndicatorAnalysisVerifyController::class);
        Route::get('/{entity}/{slug}/export', IndicatorEntitySlugExportController::class);
        Route::get('/{entity}', GetIndicatorPolygonStatusController::class);
    });
});

Route::prefix('project-pipeline')->group(function () {
    Route::get('/', GetProjectPipelineController::class);
    Route::get('/{id}', GetProjectPipelineController::class);
    Route::post('/', StoreProjectPipelineController::class);
    Route::put('/{id}', UpdateProjectPipelineController::class);
    Route::delete('/{id}', DeleteProjectPipelineController::class);
});

Route::prefix('site-polygon')->group(function () {
    Route::put('/status/bulk', ChangeStatusPolygonsController::class);
    Route::get('/{uuid}', ShowSitePolygonController::class);
    Route::post('/{uuid}/new-version', StoreSitePolygonNewVersionController::class);
    Route::put('/{uuid}/make-active', UpdateSitePolygonActiveController::class);
});

Route::get('/type-entity', EntityTypeController::class);
