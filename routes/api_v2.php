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
use App\Http\Controllers\V2\CoreTeamLeader\DeleteCoreTeamLeaderController;
use App\Http\Controllers\V2\CoreTeamLeader\StoreCoreTeamLeaderController;
use App\Http\Controllers\V2\CoreTeamLeader\UpdateCoreTeamLeaderController;
use App\Http\Controllers\V2\Dashboard\ActiveCountriesTableController;
use App\Http\Controllers\V2\Dashboard\ActiveProjectsTableController;
use App\Http\Controllers\V2\Dashboard\CountriesController;
use App\Http\Controllers\V2\Dashboard\CountryDataController;
use App\Http\Controllers\V2\Dashboard\GetJobsCreatedController;
use App\Http\Controllers\V2\Dashboard\GetPolygonsController;
use App\Http\Controllers\V2\Dashboard\GetProjectsController;
use App\Http\Controllers\V2\Dashboard\ProjectListExportController;
use App\Http\Controllers\V2\Dashboard\ProjectProfileDetailsController;
use App\Http\Controllers\V2\Dashboard\TopProjectsAndTopTreeSpeciesController;
use App\Http\Controllers\V2\Dashboard\TotalTerrafundHeaderDashboardController;
use App\Http\Controllers\V2\Dashboard\ViewProjectController;
use App\Http\Controllers\V2\Dashboard\ViewRestorationStrategyController;
use App\Http\Controllers\V2\Dashboard\ViewTreeRestorationGoalController;
use App\Http\Controllers\V2\Dashboard\VolunteersAndAverageSurvivalRateController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Entities\AdminSoftDeleteEntityController;
use App\Http\Controllers\V2\Entities\AdminStatusEntityController;
use App\Http\Controllers\V2\Entities\EntityTypeController;
use App\Http\Controllers\V2\Entities\GetRelationsForEntityController;
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
use App\Http\Controllers\V2\Files\FilePropertiesController;
use App\Http\Controllers\V2\Files\Gallery\ViewNurseryGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewNurseryReportGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewProjectGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewProjectMonitoringGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewProjectReportGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewSiteGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewSiteMonitoringGalleryController;
use App\Http\Controllers\V2\Files\Gallery\ViewSiteReportGalleryController;
use App\Http\Controllers\V2\Files\Location\NurseryImageLocationsController;
use App\Http\Controllers\V2\Files\Location\NurseryReportImageLocationsController;
use App\Http\Controllers\V2\Files\Location\ProjectImageLocationsController;
use App\Http\Controllers\V2\Files\Location\ProjectReportImageLocationsController;
use App\Http\Controllers\V2\Files\Location\SiteImageLocationsController;
use App\Http\Controllers\V2\Files\Location\SiteReportImageLocationsController;
use App\Http\Controllers\V2\Files\UploadController;
use App\Http\Controllers\V2\Forms\AdminDeleteFormSubmissionController;
use App\Http\Controllers\V2\Forms\AdminIndexFormSubmissionController;
use App\Http\Controllers\V2\Forms\CommonOptionsIndexController;
use App\Http\Controllers\V2\Forms\DeleteFormController;
use App\Http\Controllers\V2\Forms\DeleteFormQuestionController;
use App\Http\Controllers\V2\Forms\DeleteFormSectionController;
use App\Http\Controllers\V2\Forms\DeleteFormSubmissionController;
use App\Http\Controllers\V2\Forms\ExportFormSubmissionController;
use App\Http\Controllers\V2\Forms\FormOptionsLabelController;
use App\Http\Controllers\V2\Forms\FormSubmissionNextStageController;
use App\Http\Controllers\V2\Forms\IndexFormController;
use App\Http\Controllers\V2\Forms\IndexFormSubmissionController;
use App\Http\Controllers\V2\Forms\LinkedFieldListingsController;
use App\Http\Controllers\V2\Forms\PublishFormController;
use App\Http\Controllers\V2\Forms\StoreFormController;
use App\Http\Controllers\V2\Forms\StoreFormSectionController;
use App\Http\Controllers\V2\Forms\StoreFormSubmissionController;
use App\Http\Controllers\V2\Forms\SubmitFormSubmissionController;
use App\Http\Controllers\V2\Forms\UpdateFormController;
use App\Http\Controllers\V2\Forms\UpdateFormSectionController;
use App\Http\Controllers\V2\Forms\UpdateFormSubmissionController;
use App\Http\Controllers\V2\Forms\UpdateFormSubmissionStatusController;
use App\Http\Controllers\V2\Forms\ViewFormController;
use App\Http\Controllers\V2\Forms\ViewFormSubmissionController;
use App\Http\Controllers\V2\Forms\ViewMyFormSubmissionsController;
use App\Http\Controllers\V2\FundingProgramme\AdminFundingProgrammeController;
use App\Http\Controllers\V2\FundingProgramme\FundingProgrammeController;
use App\Http\Controllers\V2\FundingProgramme\UpdateFundingProgrammeStatusController;
use App\Http\Controllers\V2\FundingType\DeleteFundingTypeController;
use App\Http\Controllers\V2\FundingType\StoreFundingTypeController;
use App\Http\Controllers\V2\FundingType\UpdateFundingTypeController;
use App\Http\Controllers\V2\Geometry\GeometryController;
use App\Http\Controllers\V2\LeadershipTeam\DeleteLeadershipTeamController;
use App\Http\Controllers\V2\LeadershipTeam\StoreLeadershipTeamController;
use App\Http\Controllers\V2\LeadershipTeam\UpdateLeadershipTeamController;
use App\Http\Controllers\V2\MediaController;
use App\Http\Controllers\V2\Nurseries\AdminIndexNurseriesController;
use App\Http\Controllers\V2\Nurseries\AdminNurseriesMultiController;
use App\Http\Controllers\V2\Nurseries\CreateNurseryWithFormController;
use App\Http\Controllers\V2\Nurseries\SoftDeleteNurseryController;
use App\Http\Controllers\V2\NurseryReports\AdminIndexNurseryReportsController;
use App\Http\Controllers\V2\NurseryReports\NurseryReportsViaNurseryController;
use App\Http\Controllers\V2\Organisations\AdminApproveOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminExportOrganisationsController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationController;
use App\Http\Controllers\V2\Organisations\AdminOrganisationMultiController;
use App\Http\Controllers\V2\Organisations\AdminRejectOrganisationController;
use App\Http\Controllers\V2\Organisations\JoinExistingOrganisationController;
use App\Http\Controllers\V2\Organisations\OrganisationApprovedUsersController;
use App\Http\Controllers\V2\Organisations\OrganisationApproveUserController;
use App\Http\Controllers\V2\Organisations\OrganisationController;
use App\Http\Controllers\V2\Organisations\OrganisationListingController;
use App\Http\Controllers\V2\Organisations\OrganisationListRequestedUsersController;
use App\Http\Controllers\V2\Organisations\OrganisationRejectUserController;
use App\Http\Controllers\V2\Organisations\OrganisationRetractMyDraftController;
use App\Http\Controllers\V2\Organisations\OrganisationSubmitController;
use App\Http\Controllers\V2\Organisations\ViewOrganisationTasksController;
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
use App\Http\Controllers\V2\ProjectPitches\AdminIndexProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\DeleteProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\ExportProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\IndexProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\StoreProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\SubmitProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\UpdateProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\ViewProjectPitchController;
use App\Http\Controllers\V2\ProjectPitches\ViewProjectPitchSubmissionsController;
use App\Http\Controllers\V2\ProjectReports\AdminIndexProjectReportsController;
use App\Http\Controllers\V2\ProjectReports\ProjectReportsViaProjectController;
use App\Http\Controllers\V2\Projects\AdminIndexProjectsController;
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
use App\Http\Controllers\V2\Projects\SoftDeleteProjectController;
use App\Http\Controllers\V2\Projects\ViewAProjectsMonitoringsController;
use App\Http\Controllers\V2\Projects\ViewMyProjectsController;
use App\Http\Controllers\V2\Projects\ViewProjectMonitoringPartnersController;
use App\Http\Controllers\V2\Projects\ViewProjectNurseriesController;
use App\Http\Controllers\V2\Projects\ViewProjectSitesController;
use App\Http\Controllers\V2\Projects\ViewProjectTasksController;
use App\Http\Controllers\V2\Projects\ViewProjectTasksReportsController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminCreateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminDeleteReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminIndexReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminUpdateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkViaAccessCodeController;
use App\Http\Controllers\V2\Reports\NothingToReportReportController;
use App\Http\Controllers\V2\SiteReports\AdminIndexSiteReportsController;
use App\Http\Controllers\V2\SiteReports\SiteReportsViaSiteController;
use App\Http\Controllers\V2\Sites\AdminIndexSitesController;
use App\Http\Controllers\V2\Sites\AdminSitesMultiController;
use App\Http\Controllers\V2\Sites\CreateSiteWithFormController;
use App\Http\Controllers\V2\Sites\IndexSitePolygonVersionsController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminCreateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminSoftDeleteSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminUpdateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\ViewSiteMonitoringController;
use App\Http\Controllers\V2\Sites\ShowSitePolygonController;
use App\Http\Controllers\V2\Sites\SiteCheckApproveController;
use App\Http\Controllers\V2\Sites\SitePolygonDataController;
use App\Http\Controllers\V2\Sites\SoftDeleteSiteController;
use App\Http\Controllers\V2\Sites\StoreSitePolygonNewVersionController;
use App\Http\Controllers\V2\Sites\UpdateSitePolygonActiveController;
use App\Http\Controllers\V2\Sites\ViewASitesMonitoringsController;
use App\Http\Controllers\V2\Stages\DeleteStageController;
use App\Http\Controllers\V2\Stages\IndexStageController;
use App\Http\Controllers\V2\Stages\StoreStageController;
use App\Http\Controllers\V2\Stages\UpdateStageController;
use App\Http\Controllers\V2\Stages\UpdateStageStatusController;
use App\Http\Controllers\V2\Stages\ViewStageController;
use App\Http\Controllers\V2\Tasks\AdminIndexTasksController;
use App\Http\Controllers\V2\Tasks\SubmitProjectTasksController;
use App\Http\Controllers\V2\Tasks\ViewTaskController;
use App\Http\Controllers\V2\Terrafund\TerrafundClipGeometryController;
use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use App\Http\Controllers\V2\Terrafund\TerrafundEditGeometryController;
use App\Http\Controllers\V2\UpdateRequests\AdminIndexUpdateRequestsController;
use App\Http\Controllers\V2\UpdateRequests\AdminSoftDeleteUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminStatusUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminViewUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\EntityUpdateRequestsController;
use App\Http\Controllers\V2\User\AdminExportUsersController;
use App\Http\Controllers\V2\User\AdminResetPasswordController;
use App\Http\Controllers\V2\User\AdminUserController;
use App\Http\Controllers\V2\User\AdminUserMultiController;
use App\Http\Controllers\V2\User\AdminUsersOrganizationController;
use App\Http\Controllers\V2\User\AdminVerifyUserController;
use App\Http\Controllers\V2\User\CompleteActionController;
use App\Http\Controllers\V2\User\IndexMyActionsController;
use App\Http\Controllers\V2\User\UpdateMyBannersController;
use App\Http\Controllers\V2\UserLocaleController;
use App\Http\Controllers\V2\Workdays\GetWorkdaysForEntityController;
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
    Route::patch('/{uuid}', [MediaController::class, 'updateMedia']);
    Route::patch('project/{project}/{mediaUuid}', [MediaController::class, 'updateIsCover']);
});

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

    Route::prefix('update-requests')->group(function () {
        Route::get('', AdminIndexUpdateRequestsController::class);
        Route::delete('/{updateRequest}', AdminSoftDeleteUpdateRequestController::class);
        Route::put('/{updateRequest}/{status}', AdminStatusUpdateRequestController::class);
    });

    Route::prefix('tasks')->group(function () {
        Route::get('', AdminIndexTasksController::class);
    });

    Route::prefix('projects')->group(function () {
        Route::get('', AdminIndexProjectsController::class);
        Route::get('/multi', AdminProjectMultiController::class);
    });

    Route::prefix('project-monitorings')->group(function () {
        Route::post('/', AdminCreateProjectMonitoringController::class);
        Route::put('/{projectMonitoring}', AdminUpdateProjectMonitoringController::class);
        Route::delete('/{projectMonitoring}', AdminSoftDeleteProjectMonitoringController::class);
    });

    Route::prefix('sites')->group(function () {
        Route::get('/', AdminIndexSitesController::class);
        Route::get('/multi', AdminSitesMultiController::class);
    });

    Route::prefix('site-monitorings')->group(function () {
        Route::post('/', AdminCreateSiteMonitoringController::class);
        Route::put('/{siteMonitoring}', AdminUpdateSiteMonitoringController::class);
        Route::delete('/{siteMonitoring}', AdminSoftDeleteSiteMonitoringController::class);
    });

    Route::prefix('nurseries')->group(function () {
        Route::get('/', AdminIndexNurseriesController::class);
        Route::get('/multi', AdminNurseriesMultiController::class);
    });

    Route::get('/{entity}/export/{framework}', ExportAllMonitoredEntitiesController::class);

    ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
        Route::put('/{entity}/{status}', AdminStatusEntityController::class);
        Route::post('/{entity}/reminder', AdminSendReminderController::class);
        Route::delete('/{entity}', AdminSoftDeleteEntityController::class);
    });

    Route::get('nursery-reports', AdminIndexNurseryReportsController::class);
    Route::get('site-reports', AdminIndexSiteReportsController::class);
    Route::get('project-reports', AdminIndexProjectReportsController::class);

    Route::resource('organisations', AdminOrganisationController::class)->except('create');
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
        Route::get('export', AdminExportUsersController::class);
        Route::put('reset-password/{user}', AdminResetPasswordController::class);
        Route::patch('verify/{user}', AdminVerifyUserController::class);
        Route::get('users-organisation-list/{organisation}', AdminUsersOrganizationController::class);
    });
    Route::resource('users', AdminUserController::class);

    Route::prefix('forms')->group(function () {
        Route::post('/', StoreFormController::class);
        Route::get('/', IndexFormController::class);
        Route::post('/section', StoreFormSectionController::class);
        Route::patch('/section/{formSection}', UpdateFormSectionController::class);
        Route::delete('/section/{formSection}', DeleteFormSectionController::class);
        Route::delete('/question/{formQuestion}', DeleteFormQuestionController::class);
        Route::get('/common-options/{key}', CommonOptionsIndexController::class);
        Route::prefix('submissions')->group(function () {
            Route::get('/', AdminIndexFormSubmissionController::class);
            //            Route::get('/export', ExportFormSubmissionController::class);
            Route::get('/{form}/export', ExportFormSubmissionController::class);
            Route::prefix('{formSubmission}')->group(function () {
                Route::get('/', ViewFormSubmissionController::class)->middleware('i18n');
                Route::delete('/', AdminDeleteFormSubmissionController::class);
                Route::patch('/status', UpdateFormSubmissionStatusController::class);
            });
        });

        Route::prefix('applications')->group(function () {
            Route::get('/', AdminIndexApplicationController::class);
            Route::get('/{application}', AdminViewApplicationController::class);
            Route::delete('/{application}', AdminDeleteApplicationController::class);
            Route::get('/{fundingProgramme}/export', AdminExportApplicationController::class);
        });

        Route::prefix('{form}')->group(function () {
            Route::patch('/', UpdateFormController::class);
            Route::delete('/', DeleteFormController::class);
            Route::patch('/publish', PublishFormController::class);
            Route::get('/submissions', IndexFormSubmissionController::class);
        });
    });

    Route::prefix('project-pitches')->group(function () {
        Route::get('/', AdminIndexProjectPitchController::class);
        Route::get('/export', ExportProjectPitchController::class);
    });
});

/** NON ADMIN ROUTES */
Route::prefix('organisations')->group(function () {
    Route::get('/{organisation}/tasks', ViewOrganisationTasksController::class);
    Route::get('listing', OrganisationListingController::class);
    Route::post('join-existing', JoinExistingOrganisationController::class);
    Route::put('approve-user', OrganisationApproveUserController::class);
    Route::put('reject-user', OrganisationRejectUserController::class);
    Route::put('submit/{organisation}', OrganisationSubmitController::class);
    Route::get('user-requests/{organisation}', OrganisationListRequestedUsersController::class);
    Route::get('approved-users/{organisation}', OrganisationApprovedUsersController::class);
    Route::delete('retract-my-draft', OrganisationRetractMyDraftController::class);
});
Route::resource('organisations', OrganisationController::class);

Route::prefix('my')->group(function () {
    Route::patch('/banners', UpdateMyBannersController::class);

    Route::prefix('actions')->group(function () {
        Route::get('/', IndexMyActionsController::class);
        Route::put('/{action}/complete', CompleteActionController::class);
    });

    Route::get('/projects', ViewMyProjectsController::class);
});

Route::post('/users/resend', [AuthController::class, 'resendByEmail'])->withoutMiddleware('auth:service-api-key,api');

Route::prefix('forms')->group(function () {
    Route::get('/my/submissions', ViewMyFormSubmissionsController::class)->middleware('i18n');
    Route::prefix('submissions')->group(function () {
        Route::post('/', StoreFormSubmissionController::class);
        Route::patch('/{formSubmission}', UpdateFormSubmissionController::class);
        Route::get('/{formSubmission}', ViewFormSubmissionController::class)->middleware('i18n');
        Route::put('/submit/{formSubmission}', SubmitFormSubmissionController::class)->middleware('i18n');
        Route::post('/{formSubmission}/next-stage', FormSubmissionNextStageController::class);
        Route::delete('/{formSubmission}', DeleteFormSubmissionController::class);
    });
    Route::get('/linked-field-listing', LinkedFieldListingsController::class);
    Route::get('/option-labels', FormOptionsLabelController::class)->middleware('i18n');

    Route::get('/', IndexFormController::class);
    Route::get('/{form}', ViewFormController::class)->middleware('i18n');

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
    Route::get('/{framework}', ViewReportingFrameworkController::class);
    Route::get('/access-code/{accessCode}', ViewReportingFrameworkViaAccessCodeController::class);
});

Route::get('/my/applications', ViewMyApplicationController::class);
Route::prefix('applications')->group(function () {
    Route::get('/{application}', ViewApplicationController::class);
    Route::get('/{application}/export', ExportApplicationController::class);
});

Route::prefix('funding-programme/stage')->group(function () {
    Route::get('/', IndexStageController::class);
    Route::get('/{stage}', ViewStageController::class);
});

Route::prefix('project-pitches')->group(function () {
    Route::post('/', StoreProjectPitchController::class);
    Route::get('/', IndexProjectPitchController::class);
    Route::get('/{projectPitch}', ViewProjectPitchController::class);
    Route::patch('/{projectPitch}', UpdateProjectPitchController::class);
    Route::delete('/{projectPitch}', DeleteProjectPitchController::class);
    Route::get('/{projectPitch}/submissions', ViewProjectPitchSubmissionsController::class);
    Route::put('/submit/{projectPitch}', SubmitProjectPitchController::class);
});

Route::prefix('{relationType}')
    ->whereIn('relationType', array_keys(GetRelationsForEntityController::RELATIONS))
    ->group(function () {
        ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
            Route::get('/{entity}', GetRelationsForEntityController::class);
        });
    });

ModelInterfaceBindingMiddleware::forSlugs(['project-report', 'site-report'], function () {
    Route::get('/{entity}', GetWorkdaysForEntityController::class);
}, prefix: 'workdays');

Route::prefix('leadership-team')->group(function () {
    Route::post('/', StoreLeadershipTeamController::class);
    Route::patch('/{leadershipTeam}', UpdateLeadershipTeamController::class);
    Route::delete('/{leadershipTeam}', DeleteLeadershipTeamController::class);
});

Route::prefix('ownership-stake')->group(function () {
    Route::post('/', StoreOwnershipStakeController::class);
    Route::patch('/{ownershipStake}', UpdateOwnershipStakeController::class);
    Route::delete('/{ownershipStake}', DeleteOwnershipStakeController::class);
});

Route::prefix('core-team-leader')->group(function () {
    Route::post('/', StoreCoreTeamLeaderController::class);
    Route::patch('/{coreTeamLeader}', UpdateCoreTeamLeaderController::class);
    Route::delete('/{coreTeamLeader}', DeleteCoreTeamLeaderController::class);
});

Route::prefix('projects')->group(function () {
    Route::delete('/{project}', SoftDeleteProjectController::class);
    Route::get('/{project}/tasks', ViewProjectTasksController::class);
    Route::get('/{project}/partners', ViewProjectMonitoringPartnersController::class);
    Route::get('/{project}/sites', ViewProjectSitesController::class);
    Route::get('/{project}/site-polygons', ViewSitesPolygonsForProjectController::class);
    Route::get('/{project}/site-polygons/all', ViewAllSitesPolygonsForProjectController::class);
    Route::get('/{project}/nurseries', ViewProjectNurseriesController::class);
    Route::get('/{project}/files', ViewProjectGalleryController::class);
    Route::get('/{project}/monitorings', ViewAProjectsMonitoringsController::class);
    Route::get('/{project}/reports', ProjectReportsViaProjectController::class);
    Route::get('/{project}/image/locations', ProjectImageLocationsController::class);

    Route::post('/{project}/invite', CreateProjectInviteController::class);
    Route::post('/invite/accept', ProjectInviteAcceptController::class);

    Route::resource('/{project}/managers', ProjectManagersController::class)->only(['index', 'store', 'destroy']);

    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);

    Route::delete('/{project}/{email}/remove-partner', DeleteProjectMonitoringPartnersController::class);
});

Route::prefix('tasks')->group(function () {
    Route::get('/{task}', ViewTaskController::class);
    Route::get('/{task}/reports', ViewProjectTasksReportsController::class);
    Route::put('/{task}/submit', SubmitProjectTasksController::class);
});

ModelInterfaceBindingMiddleware::forSlugs(['site-reports', 'nursery-reports'], function () {
    Route::put('/{report}/nothing-to-report', NothingToReportReportController::class);
});

ModelInterfaceBindingMiddleware::with(EntityModel::class, function () {
    Route::get('/{entity}', ViewEntityController::class);
});

Route::prefix('project-reports')->group(function () {
    Route::get('/{projectReport}/files', ViewProjectReportGalleryController::class);
    Route::get('/{projectReport}/image/locations', ProjectReportImageLocationsController::class);
});

Route::prefix('sites/{site}')->group(function () {
    Route::get('/files', ViewSiteGalleryController::class);
    Route::get('/reports', SiteReportsViaSiteController::class);
    Route::get('/monitorings', ViewASitesMonitoringsController::class);
    Route::get('/image/locations', SiteImageLocationsController::class);
    Route::delete('/', SoftDeleteSiteController::class);
    Route::get('/export', ExportAllSiteDataAsProjectDeveloperController::class);
    Route::get('/polygon', [SitePolygonDataController::class, 'getSitePolygonData']);
    Route::get('/bbox', [SitePolygonDataController::class, 'getBboxOfCompleteSite']);
    Route::get('/check-approve', SiteCheckApproveController::class);
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

Route::prefix('project-monitorings')->group(function () {
    Route::get('/{projectMonitoring}/files', ViewProjectMonitoringGalleryController::class);
});

Route::prefix('site-monitorings')->group(function () {
    Route::get('/{siteMonitoring}', ViewSiteMonitoringController::class);
    Route::get('/{siteMonitoring}/files', ViewSiteMonitoringGalleryController::class);
});

Route::prefix('site-reports')->group(function () {
    Route::get('/{siteReport}/files', ViewSiteReportGalleryController::class);
    Route::get('/{siteReport}/image/locations', SiteReportImageLocationsController::class);
});

Route::prefix('nurseries')->group(function () {
    Route::get('/{nursery}/files', ViewNurseryGalleryController::class);
    Route::get('/{nursery}/reports', NurseryReportsViaNurseryController::class);
    Route::get('/{nursery}/image/locations', NurseryImageLocationsController::class);
    Route::delete('/{nursery}', SoftDeleteNurseryController::class);
    Route::get('/{nursery}/export', ExportAllNurseryDataAsProjectDeveloperController::class);
});

Route::prefix('nursery-reports')->group(function () {
    Route::get('/{nurseryReport}/files', ViewNurseryReportGalleryController::class);
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
    Route::post('/polygon', [TerrafundCreateGeometryController::class, 'storeGeometry']);
    Route::post('/upload-geojson', [TerrafundCreateGeometryController::class, 'uploadGeoJSONFile']);
    Route::post('/upload-shapefile', [TerrafundCreateGeometryController::class, 'uploadShapefile']);
    Route::post('/upload-kml', [TerrafundCreateGeometryController::class, 'uploadKMLFile']);
    Route::post('/upload-geojson-validate', [TerrafundCreateGeometryController::class, 'uploadGeoJSONFileWithValidation']);
    Route::post('/upload-shapefile-validate', [TerrafundCreateGeometryController::class, 'uploadShapefileWithValidation']);
    Route::post('/upload-kml-validate', [TerrafundCreateGeometryController::class, 'uploadKMLFileWithValidation']);
    Route::post('/upload-geojson-project', [TerrafundCreateGeometryController::class, 'uploadGeoJSONFileProject']);
    Route::post('/upload-shapefile-project', [TerrafundCreateGeometryController::class, 'uploadShapefileProject']);
    Route::post('/upload-kml-project', [TerrafundCreateGeometryController::class, 'uploadKMLFileProject']);

    Route::post('/polygon/{uuid}', [TerrafundCreateGeometryController::class, 'processGeometry']);
    Route::get('/geojson/complete', [TerrafundCreateGeometryController::class, 'getPolygonAsGeoJSONDownload']);
    Route::get('/geojson/site', [TerrafundCreateGeometryController::class, 'getAllPolygonsAsGeoJSONDownload']);


    Route::get('/validation/self-intersection', [TerrafundCreateGeometryController::class, 'checkSelfIntersection']);
    Route::get('/validation/size-limit', [TerrafundCreateGeometryController::class, 'validatePolygonSize']);
    Route::get('/validation/spike', [TerrafundCreateGeometryController::class, 'checkBoundarySegments']);
    Route::get('/validation/within-country', [TerrafundCreateGeometryController::class, 'checkWithinCountry']);
    Route::get('/validation/geometry-type', [TerrafundCreateGeometryController::class, 'getGeometryType']);
    Route::get('/country-names', [TerrafundCreateGeometryController::class, 'getAllCountryNames']);
    Route::get('/validation/criteria-data', [TerrafundCreateGeometryController::class, 'getCriteriaData']);
    Route::get('/validation/overlapping', [TerrafundCreateGeometryController::class, 'validateOverlapping']);
    Route::get('/validation/estimated-area', [TerrafundCreateGeometryController::class, 'validateEstimatedArea']);
    Route::get('/validation/table-data', [TerrafundCreateGeometryController::class, 'validateDataInDB']);
    Route::post('/validation/polygon', [TerrafundCreateGeometryController::class, 'getValidationPolygon']);
    Route::post('/validation/sitePolygons', [TerrafundCreateGeometryController::class, 'getSiteValidationPolygon']);
    Route::get('/validation/site', [TerrafundCreateGeometryController::class, 'getCurrentSiteValidation']);
    Route::post('/clip-polygons/site/{uuid}', [TerrafundClipGeometryController::class, 'clipOverlappingPolygonsBySite']);
    Route::post('/clip-polygons/polygon/{uuid}', [TerrafundClipGeometryController::class, 'clipOverlappingPolygons']);

    Route::get('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'getSitePolygonData']);
    Route::get('/polygon/geojson/{uuid}', [TerrafundEditGeometryController::class, 'getPolygonGeojson']);
    Route::put('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'updateGeometry']);
    Route::delete('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'deletePolygonAndSitePolygon']);

    Route::get('/project-polygon', [TerrafundEditGeometryController::class, 'getProjectPolygonData']);
    Route::delete('/project-polygon/{uuid}', [TerrafundEditGeometryController::class, 'deletePolygonAndProjectPolygon']);
    Route::delete('/project-polygons', [TerrafundEditGeometryController::class, 'deleteMultiplePolygonsAndSitePolygons']);
    Route::get('/polygon/bbox/{uuid}', [TerrafundEditGeometryController::class, 'getPolygonBbox']);

    Route::put('/site-polygon/{uuid}', [TerrafundEditGeometryController::class, 'updateSitePolygon']);
    Route::post('/site-polygon/{uuid}/{siteUuid}', [TerrafundEditGeometryController::class, 'createSitePolygon']);

    Route::post('/new-site-polygon/{uuid}/new-version', [TerrafundEditGeometryController::class, 'createSitePolygonNewVersion']);

    Route::post('/project-polygon/{uuid}/{entity_uuid}/{entity_type}', [TerrafundEditGeometryController::class, 'createProjectPolygon']);

});

Route::get('/funding-programme', [FundingProgrammeController::class, 'index'])->middleware('i18n');
Route::get('/funding-programme/{fundingProgramme}', [FundingProgrammeController::class, 'show']);

ModelInterfaceBindingMiddleware::with(
    MediaModel::class,
    function () {
        Route::post('/{collection}/{mediaModel}', UploadController::class);
        Route::post('/{collection}/{mediaModel}/bulk_url', [UploadController::class, 'bulkUrlUpload']);
    },
    prefix: 'file/upload',
    modelParameter: 'mediaModel'
);

Route::post('/export-image', ExportImageController::class);

Route::resource('files', FilePropertiesController::class);
//Route::put('file/{uuid}', [FilePropertiesController::class, 'update']);
//Route::delete('file/{uuid}', [FilePropertiesController::class, 'destroy']);

ModelInterfaceBindingMiddleware::with(AuditableModel::class, function () {
    Route::post('/{auditable}', StoreAuditStatusController::class);
    Route::get('/{auditable}', GetAuditStatusController::class);
}, prefix: 'audit-status');

ModelInterfaceBindingMiddleware::with(AuditableModel::class, function () {
    Route::put('/{auditable}/status', UpdateAuditableStatusController::class);
    Route::delete('/{auditable}/{uuid}/delete', DeleteAuditStatusController::class);
});

Route::prefix('dashboard')->group(function () {
    Route::get('/restoration-strategy', ViewRestorationStrategyController::class);
    Route::get('/jobs-created', GetJobsCreatedController::class);
    Route::get('/volunteers-survival-rate', VolunteersAndAverageSurvivalRateController::class);
    Route::get('/tree-restoration-goal', ViewTreeRestorationGoalController::class);
    Route::get('/project-list-export', ProjectListExportController::class);
    Route::get('/get-polygons', [GetPolygonsController::class, 'getPolygonsOfProject']);
    Route::get('/get-polygons/statuses', [GetPolygonsController::class, 'getPolygonsByStatusOfProject']);
    Route::get('/get-bbox-project', [GetPolygonsController::class, 'getBboxOfCompleteProject']);
    Route::get('/bbox/project', [GetPolygonsController::class, 'getProjectBbox']);
    Route::get('/country/{country}', [CountryDataController::class, 'getCountryBbox']);
    Route::get('/polygon-data/{uuid}', [CountryDataController::class, 'getPolygonData']);
    Route::get('/project-data/{uuid}', [CountryDataController::class, 'getProjectData']);
    Route::get('/active-projects', ActiveProjectsTableController::class);
    Route::get('/total-section-header', TotalTerrafundHeaderDashboardController::class);
    Route::get('/active-countries', ActiveCountriesTableController::class);
    Route::get('/countries', CountriesController::class);
    Route::get('/get-projects', GetProjectsController::class);
    Route::get('/project-details/{project}', ProjectProfileDetailsController::class);
    Route::get('/top-trees-planted', TopProjectsAndTopTreeSpeciesController::class);
    Route::get('/view-project/{uuid}', [ViewProjectController::class, 'getIfUserIsAllowedToProject']);
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
    Route::get('/{uuid}/versions', IndexSitePolygonVersionsController::class);
    Route::post('/{uuid}/new-version', StoreSitePolygonNewVersionController::class);
    Route::put('/{uuid}/make-active', UpdateSitePolygonActiveController::class);
});

Route::get('/type-entity', EntityTypeController::class);

Route::patch('/users/locale', UserLocaleController::class);
