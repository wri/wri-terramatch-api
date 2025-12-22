<?php

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\V2\Applications\AdminExportApplicationController;
use App\Http\Controllers\V2\Applications\ExportApplicationController;
use App\Http\Controllers\V2\Auditable\UpdateAuditableStatusController;
use App\Http\Controllers\V2\AuditStatus\DeleteAuditStatusController;
use App\Http\Controllers\V2\AuditStatus\GetAuditStatusController;
use App\Http\Controllers\V2\AuditStatus\StoreAuditStatusController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringImportController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringProjectController;
use App\Http\Controllers\V2\BaselineMonitoring\BaselineMonitoringSiteController;
use App\Http\Controllers\V2\Dashboard\ActiveCountriesTableController;
use App\Http\Controllers\V2\Dashboard\ActiveProjectsTableController;
use App\Http\Controllers\V2\Dashboard\CountryAndPolygonDataController;
use App\Http\Controllers\V2\Dashboard\GetJobsCreatedController;
use App\Http\Controllers\V2\Dashboard\GetPolygonsController;
use App\Http\Controllers\V2\Dashboard\ProjectListExportController;
use App\Http\Controllers\V2\Dashboard\TotalTerrafundHeaderDashboardController;
use App\Http\Controllers\V2\Dashboard\ViewProjectController;
use App\Http\Controllers\V2\Dashboard\ViewRestorationStrategyController;
use App\Http\Controllers\V2\Dashboard\VolunteersAndAverageSurvivalRateController;
use App\Http\Controllers\V2\DisturbanceReports\ExportDisturbanceReportController;
use App\Http\Controllers\V2\Entities\AdminSendReminderController;
use App\Http\Controllers\V2\Entities\EntityTypeController;
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
use App\Http\Controllers\V2\ImpactStory\ImpactStoryController;
use App\Http\Controllers\V2\Indicators\GetHectaresRestoredController;
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
use App\Http\Controllers\V2\Sites\IndexSitePolygonVersionsController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminCreateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminSoftDeleteSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\AdminUpdateSiteMonitoringController;
use App\Http\Controllers\V2\Sites\Monitoring\ViewSiteMonitoringController;
use App\Http\Controllers\V2\Sites\ShowSitePolygonController;
use App\Http\Controllers\V2\Sites\SiteCheckApproveController;
use App\Http\Controllers\V2\Sites\SitePolygonDataController;
use App\Http\Controllers\V2\Sites\StoreSitePolygonNewVersionController;
use App\Http\Controllers\V2\Sites\UpdateSitePolygonActiveController;
use App\Http\Controllers\V2\Sites\ViewASitesMonitoringsController;
use App\Http\Controllers\V2\SrpReports\ExportSrpReportController;
use App\Http\Controllers\V2\Terrafund\TerrafundClipGeometryController;
use App\Http\Controllers\V2\Terrafund\TerrafundCreateGeometryController;
use App\Http\Controllers\V2\Terrafund\TerrafundEditGeometryController;
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
    Route::patch('/{uuid}', [MediaController::class, 'updateMedia']);
    Route::patch('project/{project}/{mediaUuid}', [MediaController::class, 'updateIsCover']);
});
Route::get('impact-stories', [ImpactStoryController::class, 'index'])
    ->withoutMiddleware(['auth:service-api-key,api']);

Route::get('impact-stories/{impact_story}', [ImpactStoryController::class, 'show'])
    ->withoutMiddleware(['auth:service-api-key,api']);

Route::resource('impact-stories', ImpactStoryController::class)
    ->except(['index', 'show']);
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
    Route::post('impact-stories/bulk-delete', [ImpactStoryController::class, 'bulkDestroy']);
    Route::resource('impact-stories', ImpactStoryController::class);

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
    Route::get('/{project}/site-polygons/all', ViewAllSitesPolygonsForProjectController::class);
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
    Route::get('/polygon', [SitePolygonDataController::class, 'getSitePolygonData']);
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
    Route::get('/geojson/all-active', [TerrafundCreateGeometryController::class, 'downloadGeojsonAllActivePolygons']);
    Route::get('/geojson/all-by-framework', [TerrafundCreateGeometryController::class, 'downloadAllActivePolygonsByFramework']);
    Route::get('/geojson/all-by-landscape', [TerrafundCreateGeometryController::class, 'downloadAllPolygonsByLandscape']);

    Route::get('/validation/self-intersection', [TerrafundCreateGeometryController::class, 'checkSelfIntersection']);
    Route::get('/validation/size-limit', [TerrafundCreateGeometryController::class, 'validatePolygonSize']);
    Route::get('/validation/spike', [TerrafundCreateGeometryController::class, 'checkBoundarySegments']);
    Route::get('/validation/within-country', [TerrafundCreateGeometryController::class, 'checkWithinCountry']);
    Route::get('/validation/geometry-type', [TerrafundCreateGeometryController::class, 'getGeometryType']);
    Route::get('/country-names', [TerrafundCreateGeometryController::class, 'getAllCountryNames']);
    Route::post('/validation/criteria-data', [TerrafundCreateGeometryController::class, 'getCriteriaDataForMultiple']);
    Route::get('/validation/overlapping', [TerrafundCreateGeometryController::class, 'validateOverlapping']);
    Route::get('/validation/estimated-area', [TerrafundCreateGeometryController::class, 'validateEstimatedArea']);
    Route::get('/validation/estimated-area-project', [TerrafundCreateGeometryController::class, 'validateEstimatedAreaProject']);
    Route::get('/validation/estimated-area-site', [TerrafundCreateGeometryController::class, 'validateEstimatedAreaSite']);
    Route::get('/validation/table-data', [TerrafundCreateGeometryController::class, 'validateDataInDB']);
    Route::post('/validation/polygon', [TerrafundCreateGeometryController::class, 'sendRunValidationPolygon']);
    Route::post('/validation/polygons', [TerrafundCreateGeometryController::class, 'runPolygonsValidation']);
    Route::post('/validation/sitePolygons', [TerrafundCreateGeometryController::class, 'runSiteValidationPolygon']);
    Route::post('/clip-polygons/site/{uuid}', [TerrafundClipGeometryController::class, 'clipOverlappingPolygonsOfProjectBySite']);
    Route::post('/clip-polygons/polygon/{uuid}', [TerrafundClipGeometryController::class, 'clipOverlappingPolygon']);
    Route::post('/clip-polygons/polygons', [TerrafundClipGeometryController::class, 'clipOverlappingPolygons']);

    Route::get('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'getSitePolygonData']);
    Route::get('/polygon/geojson/{uuid}', [TerrafundEditGeometryController::class, 'getPolygonGeojson']);
    Route::put('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'updateGeometry']);
    Route::delete('/polygon/{uuid}', [TerrafundEditGeometryController::class, 'deletePolygonAndSitePolygon']);

    Route::get('/project-polygon', [TerrafundEditGeometryController::class, 'getProjectPolygonData']);
    Route::delete('/project-polygon/{uuid}', [TerrafundEditGeometryController::class, 'deletePolygonAndProjectPolygon']);
    Route::delete('/project-polygons', [TerrafundEditGeometryController::class, 'deleteMultiplePolygonsAndSitePolygons']);

    Route::put('/site-polygon/{uuid}', [TerrafundEditGeometryController::class, 'updateSitePolygon']);
    Route::post('/site-polygon/{uuid}/{siteUuid}', [TerrafundEditGeometryController::class, 'createSitePolygon']);

    Route::post('/new-site-polygon/{uuid}/new-version', [TerrafundEditGeometryController::class, 'createSitePolygonNewVersion']);

    Route::post('/project-polygon/{uuid}/{entity_uuid}/{entity_type}', [TerrafundEditGeometryController::class, 'createProjectPolygon']);

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
    Route::post('/{auditable}', StoreAuditStatusController::class);
    Route::get('/{auditable}', GetAuditStatusController::class);
}, prefix: 'audit-status');

ModelInterfaceBindingMiddleware::with(AuditableModel::class, function () {
    Route::put('/{auditable}/status', UpdateAuditableStatusController::class);
    Route::delete('/{auditable}/{uuid}/delete', DeleteAuditStatusController::class);
});

Route::prefix('dashboard')->withoutMiddleware('auth:service-api-key,api')->group(function () {
    Route::get('/restoration-strategy', ViewRestorationStrategyController::class);
    Route::get('/jobs-created', GetJobsCreatedController::class);
    Route::get('/volunteers-survival-rate', VolunteersAndAverageSurvivalRateController::class);
    Route::get('/project-list-export', ProjectListExportController::class);
    Route::get('/polygons/{poly_uuid}/centroid', [GetPolygonsController::class, 'getCentroidOfPolygon']);
    Route::get('/polygon-data/{uuid}', [CountryAndPolygonDataController::class, 'getPolygonData']);
    Route::get('/active-projects', ActiveProjectsTableController::class);
    Route::get('/total-section-header', TotalTerrafundHeaderDashboardController::class);
    Route::get('/total-section-header/country', [TotalTerrafundHeaderDashboardController::class, 'getTotalDataForCountry']);
    Route::get('/active-countries', ActiveCountriesTableController::class);
    Route::get('/view-project/{uuid}', [ViewProjectController::class, 'getIfUserIsAllowedToProject']);
    Route::get('/view-project-list', [ViewProjectController::class, 'getAllProjectsAllowedToUser']);
    Route::get('/frameworks', [ViewProjectController::class, 'getFrameworks']);
    Route::get('/indicator/hectares-restoration', GetHectaresRestoredController::class);
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
    Route::get('/{uuid}/versions', IndexSitePolygonVersionsController::class);
    Route::post('/{uuid}/new-version', StoreSitePolygonNewVersionController::class);
    Route::put('/{uuid}/make-active', UpdateSitePolygonActiveController::class);
});

Route::get('/type-entity', EntityTypeController::class);
