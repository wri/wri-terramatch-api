<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\AdminDeleteApplicationController;
use App\Http\Controllers\V2\Applications\AdminExportApplicationController;
use App\Http\Controllers\V2\Applications\AdminIndexApplicationController;
use App\Http\Controllers\V2\Applications\AdminViewApplicationController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Applications\ViewApplicationController;
use App\Http\Controllers\V2\Applications\ViewMyApplicationController;
use App\Http\Controllers\V2\Audits\AdminIndexAuditsController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringImportController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringProjectController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringSiteController;
use App\Http\Controllers\V2\CoreTeamLeader\DeleteCoreTeamLeaderController;
use App\Http\Controllers\V2\CoreTeamLeader\StoreCoreTeamLeaderController;
use App\Http\Controllers\V2\CoreTeamLeader\UpdateCoreTeamLeaderController;
use App\Http\Controllers\V2\Disturbances\DeleteDisturbanceController;
use App\Http\Controllers\V2\Disturbances\GetDisturbancesForEntityController;
use App\Http\Controllers\V2\Disturbances\StoreDisturbanceController;
use App\Http\Controllers\V2\Disturbances\UpdateDisturbanceController;
use App\Http\Controllers\V2\Exports\ExportAllMonitoredEntitiesController;
use App\Http\Controllers\V2\Exports\ExportAllNurseryDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllProjectDataAsProjectDeveloperController;
use App\Http\Controllers\V2\Exports\ExportAllSiteDataAsProjectDeveloperController;
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
use App\Http\Controllers\V2\Invasives\DeleteInvasiveController;
use App\Http\Controllers\V2\Invasives\GetInvasivesForEntityController;
use App\Http\Controllers\V2\Invasives\StoreInvasiveController;
use App\Http\Controllers\V2\Invasives\UpdateInvasiveController;
use App\Http\Controllers\V2\LeadershipTeam\DeleteLeadershipTeamController;
use App\Http\Controllers\V2\LeadershipTeam\StoreLeadershipTeamController;
use App\Http\Controllers\V2\LeadershipTeam\UpdateLeadershipTeamController;
use App\Http\Controllers\V2\MediaController;
use App\Http\Controllers\V2\Nurseries\AdminIndexNurseriesController;
use App\Http\Controllers\V2\Nurseries\AdminNurseriesMultiController;
use App\Http\Controllers\V2\Nurseries\AdminSoftDeleteNurseryController;
use App\Http\Controllers\V2\Nurseries\AdminStatusNurseryController;
use App\Http\Controllers\V2\Nurseries\CreateNurseryWithFormController;
use App\Http\Controllers\V2\Nurseries\SoftDeleteNurseryController;
use App\Http\Controllers\V2\Nurseries\SubmitNurseryWithFormController;
use App\Http\Controllers\V2\Nurseries\UpdateNurseryWithFormController;
use App\Http\Controllers\V2\Nurseries\ViewNurseryController;
use App\Http\Controllers\V2\Nurseries\ViewNurseryWithFormController;
use App\Http\Controllers\V2\NurseryReports\AdminIndexNurseryReportsController;
use App\Http\Controllers\V2\NurseryReports\AdminSoftDeleteNurseryReportController;
use App\Http\Controllers\V2\NurseryReports\AdminStatusNurseryReportController;
use App\Http\Controllers\V2\NurseryReports\CreateNurseryReportWithFormController;
use App\Http\Controllers\V2\NurseryReports\NothingToReportNurseryReportController;
use App\Http\Controllers\V2\NurseryReports\NurseryReportsViaNurseryController;
use App\Http\Controllers\V2\NurseryReports\SubmitNurseryReportWithFormController;
use App\Http\Controllers\V2\NurseryReports\UpdateNurseryReportWithFormController;
use App\Http\Controllers\V2\NurseryReports\ViewNurseryReportController;
use App\Http\Controllers\V2\NurseryReports\ViewNurseryReportWithFormController;
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
use App\Http\Controllers\V2\Polygons\ViewSitesPolygonsForProjectController;
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
use App\Http\Controllers\V2\ProjectReports\AdminSoftDeleteProjectReportController;
use App\Http\Controllers\V2\ProjectReports\AdminStatusProjectReportController;
use App\Http\Controllers\V2\ProjectReports\CreateProjectReportWithFormController;
use App\Http\Controllers\V2\ProjectReports\NothingToReportProjectReportController;
use App\Http\Controllers\V2\ProjectReports\ProjectReportsViaProjectController;
use App\Http\Controllers\V2\ProjectReports\SubmitProjectReportWithFormController;
use App\Http\Controllers\V2\ProjectReports\UpdateProjectReportWithFormController;
use App\Http\Controllers\V2\ProjectReports\ViewProjectReportController;
use App\Http\Controllers\V2\ProjectReports\ViewProjectReportWithFormController;
use App\Http\Controllers\V2\Projects\AdminIndexProjectsController;
use App\Http\Controllers\V2\Projects\AdminProjectMultiController;
use App\Http\Controllers\V2\Projects\AdminSoftDeleteProjectController;
use App\Http\Controllers\V2\Projects\AdminStatusProjectController;
use App\Http\Controllers\V2\Projects\CreateBlankProjectWithFormController;
use App\Http\Controllers\V2\Projects\CreateProjectInviteController;
use App\Http\Controllers\V2\Projects\CreateProjectWithFormController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminCreateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminSoftDeleteProjectMonitoringController;
use App\Http\Controllers\V2\Projects\Monitoring\AdminUpdateProjectMonitoringController;
use App\Http\Controllers\V2\Projects\ProjectInviteAcceptController;
use App\Http\Controllers\V2\Projects\SoftDeleteProjectController;
use App\Http\Controllers\V2\Projects\SubmitProjectWithFormController;
use App\Http\Controllers\V2\Projects\UpdateProjectWithFormController;
use App\Http\Controllers\V2\Projects\ViewAProjectsMonitoringsController;
use App\Http\Controllers\V2\Projects\ViewMyProjectsController;
use App\Http\Controllers\V2\Projects\ViewProjectController;
use App\Http\Controllers\V2\Projects\ViewProjectMonitoringPartnersController;
use App\Http\Controllers\V2\Projects\ViewProjectNurseriesController;
use App\Http\Controllers\V2\Projects\ViewProjectSitesController;
use App\Http\Controllers\V2\Projects\ViewProjectTasksController;
use App\Http\Controllers\V2\Projects\ViewProjectTasksReportsController;
use App\Http\Controllers\V2\Projects\ViewProjectWithFormController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminCreateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminDeleteReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminIndexReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\AdminUpdateReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkController;
use App\Http\Controllers\V2\ReportingFrameworks\ViewReportingFrameworkViaAccessCodeController;
use App\Http\Controllers\V2\SiteReports\AdminIndexSiteReportsController;
use App\Http\Controllers\V2\SiteReports\AdminSoftDeleteSiteReportController;
use App\Http\Controllers\V2\SiteReports\AdminStatusSiteReportController;
use App\Http\Controllers\V2\SiteReports\CreateSiteReportWithFormController;
use App\Http\Controllers\V2\SiteReports\NothingToReportSiteReportController;
use App\Http\Controllers\V2\SiteReports\SiteReportsViaSiteController;
use App\Http\Controllers\V2\SiteReports\SubmitSiteReportWithFormController;
use App\Http\Controllers\V2\SiteReports\UpdateSiteReportWithFormController;
use App\Http\Controllers\V2\SiteReports\ViewSiteReportController;
use App\Http\Controllers\V2\SiteReports\ViewSiteReportWithFormController;
use App\Http\Controllers\V2\Sites\AdminIndexSitesController;
use App\Http\Controllers\V2\Sites\AdminSitesMultiController;
use App\Http\Controllers\V2\Sites\AdminSoftDeleteSiteController;
use App\Http\Controllers\V2\Sites\AdminStatusSiteController;
use App\Http\Controllers\V2\Sites\CreateSiteWithFormController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminCreateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminSoftDeleteSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminUpdateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\ViewSiteMonitoringController;
use App\Http\Controllers\V2\Sites\SoftDeleteSiteController;
use App\Http\Controllers\V2\Sites\SubmitSiteWithFormController;
use App\Http\Controllers\V2\Sites\UpdateSiteWithFormController;
use App\Http\Controllers\V2\Sites\ViewASitesMonitoringsController;
use App\Http\Controllers\V2\Sites\ViewSiteController;
use App\Http\Controllers\V2\Sites\ViewSiteWithFormController;
use App\Http\Controllers\V2\Stages\DeleteStageController;
use App\Http\Controllers\V2\Stages\IndexStageController;
use App\Http\Controllers\V2\Stages\StoreStageController;
use App\Http\Controllers\V2\Stages\UpdateStageController;
use App\Http\Controllers\V2\Stages\UpdateStageStatusController;
use App\Http\Controllers\V2\Stages\ViewStageController;
use App\Http\Controllers\V2\Stratas\DeleteStrataController;
use App\Http\Controllers\V2\Stratas\GetStratasForEntityController;
use App\Http\Controllers\V2\Stratas\StoreStrataController;
use App\Http\Controllers\V2\Stratas\UpdateStrataController;
use App\Http\Controllers\V2\Tasks\SubmitProjectTasksController;
use App\Http\Controllers\V2\Tasks\ViewTaskController;
use App\Http\Controllers\V2\TreeSpecies\DeleteTreeSpeciesController;
use App\Http\Controllers\V2\TreeSpecies\GetTreeSpeciesForEntityController;
use App\Http\Controllers\V2\TreeSpecies\StoreTreeSpeciesController;
use App\Http\Controllers\V2\TreeSpecies\UpdateTreeSpeciesController;
use App\Http\Controllers\V2\UpdateRequests\AdminIndexUpdateRequestsController;
use App\Http\Controllers\V2\UpdateRequests\AdminSoftDeleteUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminStatusUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\AdminViewUpdateRequestController;
use App\Http\Controllers\V2\UpdateRequests\EntityUpdateRequestsController;
use App\Http\Controllers\V2\User\AdminExportUsersController;
use App\Http\Controllers\V2\User\AdminResetPasswordController;
use App\Http\Controllers\V2\User\AdminUserController;
use App\Http\Controllers\V2\User\AdminUserMultiController;
use App\Http\Controllers\V2\User\AdminVerifyUserController;
use App\Http\Controllers\V2\User\CompleteActionController;
use App\Http\Controllers\V2\User\IndexMyActionsController;
use App\Http\Controllers\V2\User\UpdateMyBannersController;
use App\Http\Controllers\V2\Workdays\DeleteWorkdayController;
use App\Http\Controllers\V2\Workdays\GetWorkdaysForEntityController;
use App\Http\Controllers\V2\Workdays\StoreWorkdayController;
use App\Http\Controllers\V2\Workdays\UpdateWorkdayController;
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
    Route::delete('/{uuid}', [MediaController::class, 'delete']);
    Route::delete('/{uuid}/{collection}', [MediaController::class, 'delete']);
});

/** ADMIN ONLY ROUTES */
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('audits/{entity}/{uuid}', AdminIndexAuditsController::class);

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

    Route::prefix('projects')->group(function () {
        Route::get('', AdminIndexProjectsController::class);
        Route::get('/multi', AdminProjectMultiController::class);
        Route::delete('/{project}', AdminSoftDeleteProjectController::class);
        Route::put('/{project}/{status}', AdminStatusProjectController::class);
    });

    Route::prefix('project-monitorings')->group(function () {
        Route::post('/', AdminCreateProjectMonitoringController::class);
        Route::put('/{projectMonitoring}', AdminUpdateProjectMonitoringController::class);
        Route::delete('/{projectMonitoring}', AdminSoftDeleteProjectMonitoringController::class);
    });

    Route::prefix('sites')->group(function () {
        Route::get('/', AdminIndexSitesController::class);
        Route::get('/multi', AdminSitesMultiController::class);
        Route::delete('/{site}', AdminSoftDeleteSiteController::class);
        Route::put('/{site}/{status}', AdminStatusSiteController::class);
    });

    Route::prefix('site-monitorings')->group(function () {
        Route::post('/', AdminCreateSiteMonitoringController::class);
        Route::put('/{siteMonitoring}', AdminUpdateSiteMonitoringController::class);
        Route::delete('/{siteMonitoring}', AdminSoftDeleteSiteMonitoringController::class);
    });

    Route::prefix('nurseries')->group(function () {
        Route::get('/', AdminIndexNurseriesController::class);
        Route::get('/multi', AdminNurseriesMultiController::class);
        Route::delete('/{nursery}', AdminSoftDeleteNurseryController::class);
        Route::put('/{nursery}/{status}', AdminStatusNurseryController::class);
    });

    Route::get('/{entity}/export/{framework}', ExportAllMonitoredEntitiesController::class);

    Route::prefix('nursery-reports')->group(function () {
        Route::get('/', AdminIndexNurseryReportsController::class);
        Route::delete('/{nurseryReport}', AdminSoftDeleteNurseryReportController::class);
        Route::put('/{nurseryReport}/{status}', AdminStatusNurseryReportController::class);
    });

    Route::prefix('site-reports')->group(function () {
        Route::get('/', AdminIndexSiteReportsController::class);
        Route::delete('/{siteReport}', AdminSoftDeleteSiteReportController::class);
        Route::put('/{siteReport}/{status}', AdminStatusSiteReportController::class);
    });

    Route::prefix('project-reports')->group(function () {
        Route::get('/', AdminIndexProjectReportsController::class);
        Route::delete('/{projectReport}', AdminSoftDeleteProjectReportController::class);
        Route::put('/{projectReport}/{status}', AdminStatusProjectReportController::class);
    });

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
                Route::get('/', ViewFormSubmissionController::class);
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
});

Route::post('/users/resend', [AuthController::class, 'resendByEmail']);

Route::prefix('forms')->group(function () {
    Route::get('/my/submissions', ViewMyFormSubmissionsController::class);
    Route::prefix('submissions')->group(function () {
        Route::post('/', StoreFormSubmissionController::class);
        Route::patch('/{formSubmission}', UpdateFormSubmissionController::class);
        Route::get('/{formSubmission}', ViewFormSubmissionController::class);
        Route::put('/submit/{formSubmission}', SubmitFormSubmissionController::class);
        Route::post('/{formSubmission}/next-stage', FormSubmissionNextStageController::class);
        Route::delete('/{formSubmission}', DeleteFormSubmissionController::class);
    });
    Route::get('/linked-field-listing', LinkedFieldListingsController::class);
    Route::get('/option-labels', FormOptionsLabelController::class);

    Route::get('/', IndexFormController::class);
    Route::get('/{form}', ViewFormController::class);

    Route::prefix('projects')->group(function () {
        Route::post('', CreateProjectWithFormController::class);
        Route::post('/{form}', CreateBlankProjectWithFormController::class);
        Route::get('/{project}', ViewProjectWithFormController::class);
        Route::put('/{project}', UpdateProjectWithFormController::class);
        Route::put('/{project}/submit', SubmitProjectWithFormController::class);
    });

    Route::prefix('project-reports')->group(function () {
        Route::post('', CreateProjectReportWithFormController::class);
        Route::get('/{projectReport}', ViewProjectReportWithFormController::class);
        Route::put('/{projectReport}', UpdateProjectReportWithFormController::class);
        Route::put('/{projectReport}/submit', SubmitProjectReportWithFormController::class);
    });

    Route::prefix('sites')->group(function () {
        Route::post('', CreateSiteWithFormController::class);
        Route::get('/{site}', ViewSiteWithFormController::class);
        Route::put('/{site}', UpdateSiteWithFormController::class);
        Route::put('/{site}/submit', SubmitSiteWithFormController::class);
    });

    Route::prefix('nurseries')->group(function () {
        Route::post('', CreateNurseryWithFormController::class);
        Route::get('/{nursery}', ViewNurseryWithFormController::class);
        Route::put('/{nursery}', UpdateNurseryWithFormController::class);
        Route::put('/{nursery}/submit', SubmitNurseryWithFormController::class);
    });

    Route::prefix('site-reports')->group(function () {
        Route::post('', CreateSiteReportWithFormController::class);
        Route::post('/{siteReport}', ViewSiteReportWithFormController::class);
        Route::get('/{siteReport}', ViewSiteReportWithFormController::class);
        Route::put('/{siteReport}', UpdateSiteReportWithFormController::class);
        Route::put('/{siteReport}/submit', SubmitSiteReportWithFormController::class);
    });

    Route::prefix('nursery-reports')->group(function () {
        Route::post('', CreateNurseryReportWithFormController::class);
        Route::get('/{nurseryReport}', ViewNurseryReportWithFormController::class);
        Route::put('/{nurseryReport}', UpdateNurseryReportWithFormController::class);
        Route::put('/{nurseryReport}/submit', SubmitNurseryReportWithFormController::class);
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

Route::prefix('tree-species')->group(function () {
    Route::post('/', StoreTreeSpeciesController::class);
    Route::patch('/{treeSpecies}', UpdateTreeSpeciesController::class);
    Route::delete('/{treeSpecies}', DeleteTreeSpeciesController::class);
    Route::get('/{entity}/{uuid}', GetTreeSpeciesForEntityController::class);
});

Route::prefix('workdays')->group(function () {
    Route::post('/', StoreWorkdayController::class);
    Route::patch('/{workday}', UpdateWorkdayController::class);
    Route::delete('/{workday}', DeleteWorkdayController::class);
    Route::get('/{entity}/{uuid}', GetWorkdaysForEntityController::class);
});

Route::prefix('stratas')->group(function () {
    Route::post('/', StoreStrataController::class);
    Route::patch('/{strata}', UpdateStrataController::class);
    Route::delete('/{strata}', DeleteStrataController::class);
    Route::get('/{entity}/{uuid}', GetStratasForEntityController::class);
});

Route::prefix('seedings')->group(function () {
    Route::post('/', \App\Http\Controllers\V2\Seedings\StoreSeedingController::class);
    Route::patch('/{seeding}', \App\Http\Controllers\V2\Seedings\UpdateSeedingController::class);
    Route::delete('/{seeding}', \App\Http\Controllers\V2\Seedings\DeleteSeedingController::class);
    Route::get('/{entity}/{uuid}', \App\Http\Controllers\V2\Seedings\GetSeedingsForEntityController::class);
});


Route::prefix('disturbances')->group(function () {
    Route::post('/', StoreDisturbanceController::class);
    Route::patch('/{disturbance}', UpdateDisturbanceController::class);
    Route::delete('/{disturbance}', DeleteDisturbanceController::class);
    Route::get('/{entity}/{uuid}', GetDisturbancesForEntityController::class);
});

Route::prefix('invasives')->group(function () {
    Route::post('/', StoreInvasiveController::class);
    Route::delete('/{invasive}', DeleteInvasiveController::class);
    Route::patch('/{invasive}', UpdateInvasiveController::class);
    Route::get('/{entity}/{uuid}', GetInvasivesForEntityController::class);
});

Route::prefix('leadership-team')->group(function () {
    Route::post('/', StoreLeadershipTeamController::class);
    Route::patch('/{leadershipTeam}', UpdateLeadershipTeamController::class);
    Route::delete('/{leadershipTeam}', DeleteLeadershipTeamController::class);
});

Route::prefix('core-team-leader')->group(function () {
    Route::post('/', StoreCoreTeamLeaderController::class);
    Route::patch('/{coreTeamLeader}', UpdateCoreTeamLeaderController::class);
    Route::delete('/{coreTeamLeader}', DeleteCoreTeamLeaderController::class);
});

Route::get('/my/projects', ViewMyProjectsController::class);
Route::prefix('projects')->group(function () {
    Route::get('/{project}', ViewProjectController::class);
    Route::delete('/{project}', SoftDeleteProjectController::class);
    Route::get('/{project}/tasks', ViewProjectTasksController::class);
    Route::get('/{project}/partners', ViewProjectMonitoringPartnersController::class);
    Route::get('/{project}/sites', ViewProjectSitesController::class);
    Route::get('/{project}/site-polygons', ViewSitesPolygonsForProjectController::class);
    Route::get('/{project}/nurseries', ViewProjectNurseriesController::class);
    Route::get('/{project}/files', ViewProjectGalleryController::class);
    Route::get('/{project}/monitorings', ViewAProjectsMonitoringsController::class);
    Route::get('/{project}/reports', ProjectReportsViaProjectController::class);
    Route::get('/{project}/image/locations', ProjectImageLocationsController::class);

    Route::post('/{project}/invite', CreateProjectInviteController::class);
    Route::post('/invite/accept', ProjectInviteAcceptController::class);

    Route::get('/{project}/export', ExportAllProjectDataAsProjectDeveloperController::class);
    Route::get('/{project}/{entity}/export', ExportProjectEntityAsProjectDeveloperController::class);
});

Route::prefix('tasks')->group(function () {
    Route::get('/{task}', ViewTaskController::class);
    Route::get('/{task}/reports', ViewProjectTasksReportsController::class);
    Route::put('/{task}/submit', SubmitProjectTasksController::class);
});

Route::prefix('project-reports')->group(function () {
    Route::get('/{projectReport}', ViewProjectReportController::class);
    Route::get('/{projectReport}/files', ViewProjectReportGalleryController::class);
    Route::get('/{projectReport}/image/locations', ProjectReportImageLocationsController::class);
    Route::put('/{projectReport}/nothing-to-report', NothingToReportProjectReportController::class);
});

Route::prefix('sites')->group(function () {
    Route::get('/{site}', ViewSiteController::class);
    Route::get('/{site}/files', ViewSiteGalleryController::class);
    Route::get('/{site}/reports', SiteReportsViaSiteController::class);
    Route::get('/{site}/monitorings', ViewASitesMonitoringsController::class);
    Route::get('/{site}/image/locations', SiteImageLocationsController::class);
    Route::delete('/{site}', SoftDeleteSiteController::class);
    Route::get('/{site}/export', ExportAllSiteDataAsProjectDeveloperController::class);
});

Route::prefix('project-monitorings')->group(function () {
    Route::get('/{projectMonitoring}/files', ViewProjectMonitoringGalleryController::class);
});

Route::prefix('site-monitorings')->group(function () {
    Route::get('/{siteMonitoring}', ViewSiteMonitoringController::class);
    Route::get('/{siteMonitoring}/files', ViewSiteMonitoringGalleryController::class);
});

Route::prefix('site-reports')->group(function () {
    Route::get('/{siteReport}', ViewSiteReportController::class);
    Route::get('/{siteReport}/files', ViewSiteReportGalleryController::class);
    Route::get('/{siteReport}/image/locations', SiteReportImageLocationsController::class);
    Route::put('/{siteReport}/nothing-to-report', NothingToReportSiteReportController::class);
});

Route::prefix('nurseries')->group(function () {
    Route::get('/{nursery}', ViewNurseryController::class);
    Route::get('/{nursery}/files', ViewNurseryGalleryController::class);
    Route::get('/{nursery}/reports', NurseryReportsViaNurseryController::class);
    Route::get('/{nursery}/image/locations', NurseryImageLocationsController::class);
    Route::delete('/{nursery}', SoftDeleteNurseryController::class);
    Route::get('/{nursery}/export', ExportAllNurseryDataAsProjectDeveloperController::class);
});

Route::prefix('nursery-reports')->group(function () {
    Route::get('/{nurseryReport}', ViewNurseryReportController::class);
    Route::get('/{nurseryReport}/files', ViewNurseryReportGalleryController::class);
    Route::get('/{nurseryReport}/image/locations', NurseryReportImageLocationsController::class);
    Route::put('/{nurseryReport}/nothing-to-report', NothingToReportNurseryReportController::class);
});

Route::get('/{entity}/{uuid}/export', ExportReportEntityAsProjectDeveloperController::class);


Route::prefix('funding-type')->group(function () {
    Route::post('/', StoreFundingTypeController::class);
    Route::patch('/{fundingType}', UpdateFundingTypeController::class);
    Route::delete('/{fundingType}', DeleteFundingTypeController::class);
});

Route::prefix('update-requests')->group(function () {
    Route::get('/{updateRequest}', AdminViewUpdateRequestController::class);
    Route::get('/{entity}/{uuid}', EntityUpdateRequestsController::class);
    Route::delete('/{updateRequest}', AdminSoftDeleteUpdateRequestController::class);
});

Route::get('/funding-programme', [FundingProgrammeController::class, 'index']);
Route::get('/funding-programme/{fundingProgramme}', [FundingProgrammeController::class, 'show']);

Route::post('file/upload/{model}/{collection}/{uuid}', UploadController::class);

Route::resource('files', FilePropertiesController::class);
//Route::put('file/{uuid}', [FilePropertiesController::class, 'update']);
//Route::delete('file/{uuid}', [FilePropertiesController::class, 'destroy']);
