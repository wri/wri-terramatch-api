<?php

use App\Http\Controllers\AdminsController;
use App\Http\Controllers\AimController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarbonCertificationsController;
use App\Http\Controllers\CarbonCertificationVersionsController;
use App\Http\Controllers\CustomExportController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\DirectSeedingController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\DraftsController;
use App\Http\Controllers\DueSubmissionController;
use App\Http\Controllers\EditHistoryController;
use App\Http\Controllers\ElevatorVideosController;
use App\Http\Controllers\FrameworkInviteCodeController;
use App\Http\Controllers\InterestsController;
use App\Http\Controllers\InvasiveController;
use App\Http\Controllers\LandTenureController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\MonitoringsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OfferContactsController;
use App\Http\Controllers\OfferDocumentsController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\OrganisationDocumentsController;
use App\Http\Controllers\OrganisationDocumentVersionsController;
use App\Http\Controllers\OrganisationFileController;
use App\Http\Controllers\OrganisationPhotoController;
use App\Http\Controllers\OrganisationsController;
use App\Http\Controllers\OrganisationVersionsController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\PitchContactsController;
use App\Http\Controllers\PitchDocumentsController;
use App\Http\Controllers\PitchDocumentVersionsController;
use App\Http\Controllers\PitchesController;
use App\Http\Controllers\PitchVersionsController;
use App\Http\Controllers\PPCSiteExportController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ProgrammeImageExportController;
use App\Http\Controllers\ProgrammeInviteController;
use App\Http\Controllers\ProgrammeShapefileExportController;
use App\Http\Controllers\ProgrammeSubmissionAdminCsvExportController;
use App\Http\Controllers\ProgrammeSubmissionCsvExportController;
use App\Http\Controllers\ProgrammeTreeSpeciesController;
use App\Http\Controllers\ProgrammeTreeSpeciesCsvController;
use App\Http\Controllers\ProgressUpdatesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RestorationMethodMetricsController;
use App\Http\Controllers\RestorationMethodMetricVersionsController;
use App\Http\Controllers\SatelliteMapsController;
use App\Http\Controllers\SatelliteMonitorController;
use App\Http\Controllers\SeedDetailController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteRestorationMethodsController;
use App\Http\Controllers\SiteSubmissionAdminCsvExportController;
use App\Http\Controllers\SiteSubmissionController;
use App\Http\Controllers\SiteSubmissionCsvExportController;
use App\Http\Controllers\SiteSubmissionDisturbanceController;
use App\Http\Controllers\SiteTreeSpeciesController;
use App\Http\Controllers\SiteTreeSpeciesCsvController;
use App\Http\Controllers\SocioeconomicBenefitsController;
use App\Http\Controllers\StratificationController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmissionMediaUploadController;
use App\Http\Controllers\TargetsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TeamMembersController;
use App\Http\Controllers\Terrafund\TerrafundAimsController;
use App\Http\Controllers\Terrafund\TerrafundCsvImportController;
use App\Http\Controllers\Terrafund\TerrafundDisturbanceController;
use App\Http\Controllers\Terrafund\TerrafundDueSubmissionController;
use App\Http\Controllers\Terrafund\TerrafundFileController;
use App\Http\Controllers\Terrafund\TerrafundNoneTreeSpeciesController;
use App\Http\Controllers\Terrafund\TerrafundNurseryController;
use App\Http\Controllers\Terrafund\TerrafundNurserySingleSubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundNurserySubmissionController;
use App\Http\Controllers\Terrafund\TerrafundNurserySubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeImageExportController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeInviteController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeNurseriesController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeSitesController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeSubmissionController;
use App\Http\Controllers\Terrafund\TerrafundProgrammeSubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundSingleNurserySubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundSingleSiteSubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundSiteController;
use App\Http\Controllers\Terrafund\TerrafundSiteSingleSubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundSiteSubmissionController;
use App\Http\Controllers\Terrafund\TerrafundSiteSubmissionCsvExportController;
use App\Http\Controllers\Terrafund\TerrafundTreeSpeciesController;
use App\Http\Controllers\TerrafundProgrammeShapefileExportController;
use App\Http\Controllers\TreeSpeciesController;
use App\Http\Controllers\TreeSpeciesVersionsController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\UsersController;
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
        Route::post('/auth/login', [AuthController::class, 'loginAction']);
        Route::get('/auth/resend', [AuthController::class, 'resendAction']);
        Route::post('/auth/reset', [AuthController::class, 'resetAction']);
        Route::patch('/auth/change', [AuthController::class, 'changeAction']);
        Route::patch('/v2/auth/verify', [AuthController::class, 'verifyUnauthorizedAction']);
    });

    Route::get('/auth/logout', [AuthController::class, 'logoutAction']);
    Route::get('/auth/refresh', [AuthController::class, 'refreshAction']);

    Route::post('/users', [UsersController::class, 'createAction']);
});

Route::patch('/auth/verify', [AuthController::class, 'verifyAction']);
Route::delete('/auth/delete_me', [AuthController::class, 'deleteMeAction']);
Route::get('/auth/me', [AuthController::class, 'meAction']);

Route::post('/uploads', [UploadsController::class, 'createAction']);
Route::put('/uploads/{upload}/update', [UploadsController::class, 'updateAction']);
Route::post('/uploads/socioeconomic_benefits', [SocioeconomicBenefitsController::class, 'uploadAction']);
Route::patch('/uploads/socioeconomic_benefits', [SocioeconomicBenefitsController::class, 'updateAction']);
Route::get('/uploads/socioeconomic_benefits/template', [SocioeconomicBenefitsController::class, 'downloadTemplateAction']);
Route::get('/uploads/socioeconomic_benefits/template/csv', [SocioeconomicBenefitsController::class, 'downloadCsvTemplateAction']);
Route::get('/uploads/socioeconomic_benefits/template/programme_submission', [SocioeconomicBenefitsController::class, 'downloadProgrammeSubmissionTemplateAction']);
Route::get('/uploads/socioeconomic_benefits/template/site_submission', [SocioeconomicBenefitsController::class, 'downloadSiteSubmissionTemplateAction']);
Route::get('/uploads/stratification/example', [StratificationController::class, 'downloadTemplateAction']);
Route::post('/uploads/site_programme_media', [MediaUploadController::class, 'createAction']);

Route::get('/organisations/{id}/users', [UsersController::class, 'readAllByOrganisationAction']);
Route::get('/users/all', [UsersController::class, 'readAllAction']);
Route::get('/users/unverified', [UsersController::class, 'readAllUnverifiedAction']);
Route::post('/users/invite', [UsersController::class, 'inviteAction']);
Route::post('/users/accept', [UsersController::class, 'acceptAction']);
Route::get('/users/{id}', [UsersController::class, 'readAction']);
Route::patch('/users/{id}', [UsersController::class, 'updateAction']);
Route::patch('/users/{user}/role', [UsersController::class, 'updateRoleAction']);
Route::get('/organisations/{id}/users/inspect', [UsersController::class, 'inspectByOrganisationAction']);
Route::post('/users/resend', [UsersController::class, 'resendVerificationEmailAction']);

Route::get('/countries', [DataController::class, 'readAllCountriesAction']);

Route::post('/organisations', [OrganisationsController::class, 'createAction']);
Route::get('/organisations/{id}', [OrganisationsController::class, 'readAction']);
Route::get('/organisations/{id}/inspect', [OrganisationsController::class, 'inspectAction']);
Route::get('/organisations', [OrganisationsController::class, 'readAllAction']);
Route::patch('/organisations/{id}', [OrganisationsController::class, 'updateAction']);
Route::get('/organisations/{organisation}/files', [OrganisationFileController::class, 'readByOrganisationAction']);
Route::post('/organisations/photo', [OrganisationPhotoController::class, 'createAction']);
Route::delete('/organisations/photo/{organisationPhoto}', [OrganisationPhotoController::class, 'deleteAction']);
Route::post('/organisations/file', [OrganisationFileController::class, 'createAction']);
Route::delete('/organisations/file/{organisationFile}', [OrganisationFileController::class, 'deleteAction']);

Route::get('/organisations/{id}/organisation_versions', [OrganisationVersionsController::class, 'readAllByOrganisationAction']);
Route::get('/organisation_versions/{id}', [OrganisationVersionsController::class, 'readAction']);
Route::patch('/organisation_versions/{id}/approve', [OrganisationVersionsController::class, 'approveAction']);
Route::patch('/organisation_versions/{id}/reject', [OrganisationVersionsController::class, 'rejectAction']);
Route::patch('/organisation_versions/{id}/revive', [OrganisationVersionsController::class, 'reviveAction']);
Route::delete('/organisation_versions/{id}', [OrganisationVersionsController::class, 'deleteAction']);

Route::get('/organisations/{organisation}/terrafund/programmes', [TerrafundProgrammeController::class, 'readAllForOrgAction']);

Route::get('/organisation_types', [DataController::class, 'readAllOrganisationTypesAction']);

Route::get('/organisations/{id}/team_members', [TeamMembersController::class, 'readAllByOrganisationAction']);
Route::post('/team_members', [TeamMembersController::class, 'createAction']);
Route::get('/team_members/{id}', [TeamMembersController::class, 'readAction']);
Route::patch('/team_members/{id}', [TeamMembersController::class, 'updateAction']);
Route::delete('/team_members/{id}', [TeamMembersController::class, 'deleteAction']);
Route::get('/organisations/{id}/team_members/inspect', [TeamMembersController::class, 'inspectByOrganisationAction']);

Route::get('/organisation_categories', [DataController::class, 'readAllOrganisationCategoriesAction']);

Route::get('/admins', [AdminsController::class, 'readAllAction']);
Route::post('/admins/invite', [AdminsController::class, 'inviteAction']);
Route::post('/admins/accept', [AdminsController::class, 'acceptAction']);
Route::get('/admins/{id}', [AdminsController::class, 'readAction']);
Route::patch('/admins/{id}', [AdminsController::class, 'updateAction']);

Route::get('/document_types', [DataController::class, 'readAllDocumentTypesAction']);

Route::get('/land_types', [DataController::class, 'readAllLandTypesAction']);

Route::get('/organisations/{organisation}/organisation_documents', [OrganisationDocumentsController::class, 'readAllByOrganisationAction']);
Route::get('/organisations/{organisation}/organisation_documents/inspect', [OrganisationDocumentsController::class, 'inspectByOrganisationAction']);
Route::post('/organisation_documents', [OrganisationDocumentsController::class, 'createAction']);
Route::get('/organisation_documents/{id}', [OrganisationDocumentsController::class, 'readAction']);
Route::patch('/organisation_documents/{id}', [OrganisationDocumentsController::class, 'updateAction']);
Route::delete('/organisation_documents/{organisationDocument}', [OrganisationDocumentsController::class, 'deleteAction']);

Route::get('/organisation_documents/{id}/organisation_document_versions', [OrganisationDocumentVersionsController::class, 'readAllByOrganisationDocumentAction']);
Route::get('/organisation_document_versions/{id}', [OrganisationDocumentVersionsController::class, 'readAction']);
Route::patch('/organisation_document_versions/{id}/approve', [OrganisationDocumentVersionsController::class, 'approveAction']);
Route::patch('/organisation_document_versions/{id}/reject', [OrganisationDocumentVersionsController::class, 'rejectAction']);
Route::patch('/organisation_document_versions/{id}/revive', [OrganisationDocumentVersionsController::class, 'reviveAction']);
Route::delete('/organisation_document_versions/{id}', [OrganisationDocumentVersionsController::class, 'deleteAction']);

Route::get('/organisations/{organisation}/offers', [OffersController::class, 'readAllByOrganisationAction']);
Route::get('/organisations/{organisation}/offers/inspect', [OffersController::class, 'inspectByOrganisationAction']);
Route::post('/offers', [OffersController::class, 'createAction']);
Route::get('/offers/most_recent', [OffersController::class, 'mostRecentAction']);
Route::get('/offers/{offer}', [OffersController::class, 'readAction']);
Route::patch('/offers/{offer}', [OffersController::class, 'updateAction']);
Route::patch('/offers/{offer}/visibility', [OffersController::class, 'updateVisibilityAction']);

Route::get('/land_ownerships', [DataController::class, 'readAllLandOwnershipsAction']);

Route::get('/offers/{offer}/offer_documents', [OfferDocumentsController::class, 'readAllByOfferAction']);
Route::post('/offer_documents', [OfferDocumentsController::class, 'createAction']);
Route::get('/offer_documents/{offerDocument}', [OfferDocumentsController::class, 'readAction']);
Route::patch('/offer_documents/{offerDocument}', [OfferDocumentsController::class, 'updateAction']);
Route::delete('/offer_documents/{offerDocument}', [OfferDocumentsController::class, 'deleteAction']);

Route::get('/reporting_levels', [DataController::class, 'readAllReportingLevelsAction']);

Route::get('/reporting_frequencies', [DataController::class, 'readAllReportingFrequenciesAction']);

Route::get('/offers/{offer}/offer_contacts', [OfferContactsController::class, 'readAllByOfferAction']);
Route::post('/offer_contacts', [OfferContactsController::class, 'createAction']);
Route::delete('/offer_contacts/{offerContact}', [OfferContactsController::class, 'deleteAction']);

Route::get('/sustainable_development_goals', [DataController::class, 'readAllSustainableDevelopmentGoalsAction']);

Route::get('/continents', [DataController::class, 'readAllContinentsAction']);

Route::get('/organisations/{organisation}/pitches', [PitchesController::class, 'readAllByOrganisationAction']);
Route::get('/organisations/{organisation}/pitches/inspect', [PitchesController::class, 'inspectByOrganisationAction']);
Route::post('/pitches', [PitchesController::class, 'createAction']);
Route::get('/pitches/most_recent', [PitchesController::class, 'mostRecentAction']);
Route::get('/continents/pitches', [PitchesController::class, 'countByContinentAction']);
Route::get('/continents/{continent}/pitches', [PitchesController::class, 'readAllByContinentAction']);
Route::get('/pitches/{id}', [PitchesController::class, 'readAction']);
Route::patch('/pitches/{id}', [PitchesController::class, 'updateAction']);
Route::patch('/pitches/{id}/visibility', [PitchesController::class, 'updateVisibilityAction']);

Route::get('/restoration_goals', [DataController::class, 'readAllRestorationGoalsAction']);

Route::get('/pitches/{id}/pitch_versions', [PitchVersionsController::class, 'readAllByPitchAction']);
Route::get('/pitch_versions/{id}', [PitchVersionsController::class, 'readAction']);
Route::patch('/pitch_versions/{id}/approve', [PitchVersionsController::class, 'approveAction']);
Route::patch('/pitch_versions/{id}/reject', [PitchVersionsController::class, 'rejectAction']);
Route::patch('/pitch_versions/{id}/revive', [PitchVersionsController::class, 'reviveAction']);
Route::delete('/pitch_versions/{id}', [PitchVersionsController::class, 'deleteAction']);

Route::get('/revenue_drivers', [DataController::class, 'readAllRevenueDriversAction']);

Route::get('/pitches/{id}/carbon_certifications', [CarbonCertificationsController::class, 'readAllByPitchAction']);
Route::get('/pitches/{id}/carbon_certifications/inspect', [CarbonCertificationsController::class, 'inspectByPitchAction']);
Route::post('/carbon_certifications', [CarbonCertificationsController::class, 'createAction']);
Route::get('/carbon_certifications/{id}', [CarbonCertificationsController::class, 'readAction']);
Route::patch('/carbon_certifications/{id}', [CarbonCertificationsController::class, 'updateAction']);
Route::delete('/carbon_certifications/{id}', [CarbonCertificationsController::class, 'deleteAction']);

Route::get('/carbon_certifications/{id}/carbon_certification_versions', [CarbonCertificationVersionsController::class, 'readAllByCarbonCertificationAction']);
Route::get('/carbon_certification_versions/{id}', [CarbonCertificationVersionsController::class, 'readAction']);
Route::patch('/carbon_certification_versions/{id}/approve', [CarbonCertificationVersionsController::class, 'approveAction']);
Route::patch('/carbon_certification_versions/{id}/reject', [CarbonCertificationVersionsController::class, 'rejectAction']);
Route::patch('/carbon_certification_versions/{id}/revive', [CarbonCertificationVersionsController::class, 'reviveAction']);
Route::delete('/carbon_certification_versions/{id}', [CarbonCertificationVersionsController::class, 'deleteAction']);

Route::get('/restoration_methods', [DataController::class, 'readAllRestorationMethodsAction']);

Route::get('/pitches/{id}/tree_species', [TreeSpeciesController::class, 'readAllByPitchAction']);
Route::get('/pitches/{id}/tree_species/inspect', [TreeSpeciesController::class, 'inspectByPitchAction']);
Route::post('/tree_species', [TreeSpeciesController::class, 'createAction']);
Route::get('/tree_species/{id}', [TreeSpeciesController::class, 'readAction']);
Route::patch('/tree_species/{id}', [TreeSpeciesController::class, 'updateAction']);
Route::delete('/tree_species/{id}', [TreeSpeciesController::class, 'deleteAction']);

Route::get('/tree_species/{id}/tree_species_versions', [TreeSpeciesVersionsController::class, 'readAllByTreeSpeciesAction']);
Route::get('/tree_species_versions/{id}', [TreeSpeciesVersionsController::class, 'readAction']);
Route::patch('/tree_species_versions/{id}/approve', [TreeSpeciesVersionsController::class, 'approveAction']);
Route::patch('/tree_species_versions/{id}/reject', [TreeSpeciesVersionsController::class, 'rejectAction']);
Route::patch('/tree_species_versions/{id}/revive', [TreeSpeciesVersionsController::class, 'reviveAction']);
Route::delete('/tree_species_versions/{id}', [TreeSpeciesVersionsController::class, 'deleteAction']);

Route::get('/funding_sources', [DataController::class, 'readAllFundingSourcesAction']);

Route::get('/pitches/{pitch}/pitch_documents', [PitchDocumentsController::class, 'readAllByPitchAction']);
Route::get('/pitches/{pitch}/pitch_documents/inspect', [PitchDocumentsController::class, 'inspectByPitchAction']);
Route::post('/pitch_documents', [PitchDocumentsController::class, 'createAction']);
Route::get('/pitch_documents/{id}', [PitchDocumentsController::class, 'readAction']);
Route::patch('/pitch_documents/{id}', [PitchDocumentsController::class, 'updateAction']);
Route::delete('/pitch_documents/{pitchDocument}', [PitchDocumentsController::class, 'deleteAction']);

Route::get('/pitch_documents/{id}/pitch_document_versions', [PitchDocumentVersionsController::class, 'readAllByPitchDocumentAction']);
Route::get('/pitch_document_versions/{id}', [PitchDocumentVersionsController::class, 'readAction']);
Route::patch('/pitch_document_versions/{id}/approve', [PitchDocumentVersionsController::class, 'approveAction']);
Route::patch('/pitch_document_versions/{id}/reject', [PitchDocumentVersionsController::class, 'rejectAction']);
Route::patch('/pitch_document_versions/{id}/revive', [PitchDocumentVersionsController::class, 'reviveAction']);
Route::delete('/pitch_document_versions/{id}', [PitchDocumentVersionsController::class, 'deleteAction']);

Route::get('/pitches/{pitch}/restoration_method_metrics', [RestorationMethodMetricsController::class, 'readAllByPitchAction']);
Route::get('/pitches/{pitch}/restoration_method_metrics/inspect', [RestorationMethodMetricsController::class, 'inspectByPitchAction']);
Route::post('/restoration_method_metrics', [RestorationMethodMetricsController::class, 'createAction']);
Route::get('/restoration_method_metrics/{id}', [RestorationMethodMetricsController::class, 'readAction']);
Route::patch('/restoration_method_metrics/{id}', [RestorationMethodMetricsController::class, 'updateAction']);
Route::delete('/restoration_method_metrics/{restorationMethodMetric}', [RestorationMethodMetricsController::class, 'deleteAction']);

Route::get('/restoration_method_metrics/{id}/restoration_method_metric_versions', [RestorationMethodMetricVersionsController::class, 'readAllByRestorationMethodMetricAction']);
Route::get('/restoration_method_metric_versions/{id}', [RestorationMethodMetricVersionsController::class, 'readAction']);
Route::patch('/restoration_method_metric_versions/{id}/approve', [RestorationMethodMetricVersionsController::class, 'approveAction']);
Route::patch('/restoration_method_metric_versions/{id}/reject', [RestorationMethodMetricVersionsController::class, 'rejectAction']);
Route::patch('/restoration_method_metric_versions/{id}/revive', [RestorationMethodMetricVersionsController::class, 'reviveAction']);
Route::delete('/restoration_method_metric_versions/{id}', [RestorationMethodMetricVersionsController::class, 'deleteAction']);

Route::get('/carbon_certification_types', [DataController::class, 'readAllCarbonCertificationTypesAction']);

Route::get('/pitches/{pitch}/pitch_contacts', [PitchContactsController::class, 'readAllByPitchAction']);
Route::post('/pitch_contacts', [PitchContactsController::class, 'createAction']);
Route::delete('/pitch_contacts/{pitchContact}', [PitchContactsController::class, 'deleteAction']);

Route::get('/tasks/organisations', [TasksController::class, 'readAllOrganisationsAction']);
Route::get('/tasks/pitches', [TasksController::class, 'readAllPitchesAction']);
Route::get('/tasks/matches', [TasksController::class, 'readAllMatchesAction']);
Route::get('/tasks/monitorings', [TasksController::class, 'readAllMonitoringsAction']);

Route::post('/pitches/search', [PitchesController::class, 'searchAction']);
Route::post('/offers/search', [OffersController::class, 'searchAction']);

Route::get('/land_sizes', [DataController::class, 'readAllLandSizesAction']);

Route::post('/interests', [InterestsController::class, 'createAction']);
Route::get('/interests/{type}', [InterestsController::class, 'readAllByTypeAction'])->where('type', 'initiated|received');
Route::delete('/interests/{interest}', [InterestsController::class, 'deleteAction']);

Route::get('/matches', [MatchesController::class, 'readAllAction']);
Route::get('/matches/{matched}', [MatchesController::class, 'readAction']);

Route::get('/notifications', [NotificationsController::class, 'readAllAction']);
Route::patch('/notifications/{notification}/mark', [NotificationsController::class, 'markAction']);

Route::post('/devices', [DevicesController::class, 'createAction']);
Route::get('/devices/{id}', [DevicesController::class, 'readAction']);
Route::get('/devices', [DevicesController::class, 'readAllAction']);
Route::patch('/devices/{id}', [DevicesController::class, 'updateAction']);
Route::delete('/devices/{id}', [DevicesController::class, 'deleteAction']);

Route::get('/reports/organisations', [ReportsController::class, 'readAllOrganisationsAction']);
Route::get('/reports/pitches', [ReportsController::class, 'readAllPitchesAction']);
Route::get('/reports/approved_organisations', [ReportsController::class, 'readAllApprovedOrganisationsAction']);
Route::get('/reports/rejected_organisations', [ReportsController::class, 'readAllRejectedOrganisationsAction']);
Route::get('/reports/approved_pitches', [ReportsController::class, 'readAllApprovedPitchesAction']);
Route::get('/reports/rejected_pitches', [ReportsController::class, 'readAllRejectedPitchesAction']);
Route::get('/reports/offers', [ReportsController::class, 'readAllOffersAction']);
Route::get('/reports/users', [ReportsController::class, 'readAllUsersAction']);
Route::get('/reports/filter_records', [ReportsController::class, 'readAllFilterRecordsAction']);
Route::get('/reports/interests', [ReportsController::class, 'readAllInterestsAction']);
Route::get('/reports/matches', [ReportsController::class, 'readAllMatchesAction']);
Route::get('/reports/monitorings', [ReportsController::class, 'readAllMonitoringsAction']);
Route::get('/reports/progress_updates', [ReportsController::class, 'readAllProgressUpdatesAction']);

Route::get('/rejected_reasons', [DataController::class, 'readAllRejectedReasonsAction']);

Route::get('/funding_brackets', [DataController::class, 'readAllFundingBracketsAction']);

Route::post('/elevator_videos', [ElevatorVideosController::class, 'createAction']);
Route::get('/elevator_videos/{elevatorVideo}', [ElevatorVideosController::class, 'readAction']);

Route::post('/drafts', [DraftsController::class, 'createAction']);
Route::get('/drafts/{type}', [DraftsController::class, 'readAllByTypeAction'])->where('type', 'offers|pitches|programmes|sites|site_submissions|programme_submissions|terrafund_programmes|terrafund_nurserys|terrafund_sites|organisations|terrafund_nursery_submissions|terrafund_site_submissions|terrafund_programme_submissions');
Route::get('/drafts/{draft}', [DraftsController::class, 'readAction']);
Route::patch('/drafts/merge', [DraftsController::class, 'mergeAction']);
Route::patch('/drafts/{draft}', [DraftsController::class, 'updateAction']);
Route::delete('/drafts/{draft}', [DraftsController::class, 'deleteAction']);
Route::patch('/drafts/{draft}/publish', [DraftsController::class, 'publishAction']);

Route::get('/visibilities', [DataController::class, 'readAllVisibilitiesAction']);

Route::post('/monitorings', [MonitoringsController::class, 'createAction']);
Route::get('/monitorings', [MonitoringsController::class, 'readAllAction']);
Route::get('/monitorings/{monitoring}', [MonitoringsController::class, 'readAction']);
Route::get('/offers/{offer}/monitorings', [MonitoringsController::class, 'readAllByOfferAction']);
Route::get('/pitches/{pitch}/monitorings', [MonitoringsController::class, 'readAllByPitchAction']);
Route::get('/monitorings/{id}/summarise', [MonitoringsController::class, 'summariseAction']);
Route::get('/monitorings/{id}/land_geojson', [MonitoringsController::class, 'readLandGeoJsonAction']);

Route::post('/targets', [TargetsController::class, 'createAction']);
Route::get('/targets/{id}', [TargetsController::class, 'readAction']);
Route::get('/monitorings/{id}/targets', [TargetsController::class, 'readAllByMonitoringAction']);
Route::get('/monitorings/{id}/targets/accepted', [TargetsController::class, 'readAcceptedByMonitoringAction']);
Route::patch('/targets/{id}/accept', [TargetsController::class, 'acceptAction']);

Route::post('/progress_updates', [ProgressUpdatesController::class, 'createAction']);
Route::get('/progress_updates/{progressUpdate}', [ProgressUpdatesController::class, 'readAction']);
Route::get('/monitorings/{id}/progress_updates', [ProgressUpdatesController::class, 'readAllByMonitoringAction']);

Route::post('/satellite_maps', [SatelliteMapsController::class, 'createAction']);
Route::get('/monitorings/{id}/satellite_maps', [SatelliteMapsController::class, 'readAllByMonitoringAction']);
Route::get('/satellite_maps/{id}', [SatelliteMapsController::class, 'readAction']);
Route::get('/monitorings/{id}/satellite_maps/latest', [SatelliteMapsController::class, 'readLatestByMonitoringAction']);

Route::post('/satellite_monitor', [SatelliteMonitorController::class, 'createAction']);
Route::get('/satellite_monitor/programme/{programme}', [SatelliteMonitorController::class, 'readAllByProgramme']);
Route::get('/satellite_monitor/programme/{programme}/latest', [SatelliteMonitorController::class, 'readLatestByProgramme']);
Route::get('/satellite_monitor/terrafund_programme/{terrafundProgramme}', [SatelliteMonitorController::class, 'readAllByTerrafundProgramme']);
Route::get('/satellite_monitor/terrafund_programme/{terrafundProgramme}/latest', [SatelliteMonitorController::class, 'readLatestByTerrafundProgramme']);
Route::get('/satellite_monitor/site/{site}', [SatelliteMonitorController::class, 'readAllBySite']);
Route::get('/satellite_monitor/site/{site}/latest', [SatelliteMonitorController::class, 'readLatestBySite']);

Route::get('/programme/submission/due', [DueSubmissionController::class, 'readAllDueProgrammeSubmissionsForUserAction']);
Route::get('/site/submission/due', [DueSubmissionController::class, 'readAllDueSiteSubmissionsForUserAction']);

Route::get('/pending/programme', [PendingController::class, 'readPendingProgrammeSubmissionsAction']);
Route::get('/pending/site', [PendingController::class, 'readPendingSiteSubmissionsAction']);

Route::post('/programme', [ProgrammeController::class, 'createAction']);
Route::patch('/programme/{programme}', [ProgrammeController::class, 'updateAction']);
Route::prefix('programme')->group(function () {
    Route::get('/{programme}/overview', [ProgrammeController::class, 'readAction']);
    Route::post('/boundary', [ProgrammeController::class, 'addBoundaryToProgrammeAction']);

    Route::post('/{programme}/submission', [SubmissionController::class, 'createAction']);
    Route::get('/{programme}/submissions', [SubmissionController::class, 'readByProgrammeAction']);
    Route::patch('/submission/{submission}', [SubmissionController::class, 'updateAction']);
    Route::get('/submission/{submission}', [SubmissionController::class, 'readAction']);
    Route::patch('/submission/{submission}/approve', [SubmissionController::class, 'approveAction']);
    Route::get('/{programme}/aims', [AimController::class, 'readAction']);
    Route::post('/{programme?}/aims', [AimController::class, 'updateAction']);

    Route::get('/{programme}/tree_species', [ProgrammeTreeSpeciesController::class, 'readAllByProgrammeAction']);
    Route::post('/tree_species', [ProgrammeTreeSpeciesController::class, 'createAction']);
    Route::post('/submission/{submission}/tree_species/bulk', [ProgrammeTreeSpeciesController::class, 'createBulkForSubmissionAction']);
    Route::post('/{programme}/tree_species/bulk', [ProgrammeTreeSpeciesController::class, 'createBulkAction']);

    Route::post('/tree_species/csv', [ProgrammeTreeSpeciesCsvController::class, 'createAction']);
    Route::post('/{programme}/tree_species/manual', [ProgrammeTreeSpeciesController::class, 'createAction']);
    Route::post('/{programme}/tree_species/csv', [ProgrammeTreeSpeciesCsvController::class, 'createAction']);
    Route::get('/tree_species/csv/{csvImport}', [ProgrammeTreeSpeciesCsvController::class, 'readAction']);
    Route::get('/tree_species/csv/{csvImport}/trees', [ProgrammeTreeSpeciesCsvController::class, 'readTreeSpeciesAction']);
    Route::delete('/tree_species/{programmeTreeSpecies}', [ProgrammeTreeSpeciesController::class, 'deleteAction']);

    Route::get('/{programme}/sites', [SiteController::class, 'readAllByProgrammeAction']);
    Route::get('/{programme}/all-sites', [SiteController::class, 'readAllNonPaginatedByProgrammeAction']);
    Route::get('/{programme}/site-metrics', [SiteController::class, 'readAllMetricsByProgrammeAction']);

    Route::post('/{programme}/invite', [ProgrammeInviteController::class, 'createAction']);
    Route::post('/invite/accept', [ProgrammeInviteController::class, 'acceptAction']);
    Route::delete('/invite/remove', [ProgrammeInviteController::class, 'removeUserAction']);
    Route::delete('/invite/{programmeInvite}', [ProgrammeInviteController::class, 'deleteAction']);
    Route::get('/{programme}/partners', [ProgrammeInviteController::class, 'readAllAction']);
});

Route::get('/programmes', [ProgrammeController::class, 'readAllAction']);
Route::prefix('programmes')->group(function () {
    Route::get('/personal', [ProgrammeController::class, 'readAllPersonalAction']);
    Route::post('/tree_species/search', [ProgrammeTreeSpeciesController::class, 'searchTreeSpeciesAction']);
    Route::get('/tree_species', [ProgrammeTreeSpeciesController::class, 'readAllAction']);
});

Route::prefix('framework')->group(function () {
    Route::post('/access_code', [FrameworkInviteCodeController::class, 'createAction']);
    Route::post('/access_code/join', [FrameworkInviteCodeController::class, 'joinAction']);
    Route::get('/access_code/all', [FrameworkInviteCodeController::class, 'readAllAction']);
    Route::delete('/access_code/{id}', [FrameworkInviteCodeController::class, 'deleteAction']);
});

Route::get('/sites', [SiteController::class, 'readAllAction']);
Route::get('/my/sites', [SiteController::class, 'readAllForUserAction']);
Route::get('/sites/tree_species', [SiteTreeSpeciesController::class, 'readAllAction']);
Route::get('/sites/exporter', PPCSiteExportController::class);

Route::prefix('document_files')->group(function () {
    Route::get('/template/{templateName}', [DocumentFileController::class, 'downloadExample']);
    Route::get('/file/{uuid}', [DocumentFileController::class, 'readAction']);
    Route::post('/file', [DocumentFileController::class, 'createAction']);
    Route::put('/{uuid}', [DocumentFileController::class, 'updateAction']);
    Route::delete('/{uuid}', [DocumentFileController::class, 'deleteAction']);
});

Route::post('/site', [SiteController::class, 'createAction']);
Route::patch('/site/{site}', [SiteController::class, 'updateAction']);
Route::prefix('site')->group(function () {
    Route::get('/{site}/tree_species', [SiteTreeSpeciesController::class, 'readAllBySiteAction']);
    Route::post('/{site}/tree_species/bulk', [SiteTreeSpeciesController::class, 'createBulkAction']);
    Route::get('/{site}/overview', [SiteController::class, 'readAction']);
    Route::post('/boundary', [SiteController::class, 'addBoundaryToSiteAction']);
    Route::get('/restoration_methods', [SiteRestorationMethodsController::class, 'readAllAction']);
    Route::get('/land_tenures', [LandTenureController::class, 'readAllAction']);
    Route::post('/{site}/restoration_methods', [SiteController::class, 'attachRestorationMethodsAction']);
    Route::post('/{site?}/establishment_date', [SiteController::class, 'updateEstablishmentDateAction']);
    Route::post('/{site}/narrative', [SiteController::class, 'createNarrativeAction']);
    Route::post('/{site}/land_tenure', [SiteController::class, 'attachLandTenureAction']);
    Route::post('/{site?}/aims', [SiteController::class, 'createAimAction']);
    Route::post('/{site?}/seeds', [SeedDetailController::class, 'createAction']);
    Route::post('/{site}/seeds/bulk', [SeedDetailController::class, 'createBulkAction']);
    Route::post('/{site?}/invasives', [InvasiveController::class, 'createAction']);

    Route::get('/tree_species/template/csv', [SiteTreeSpeciesCsvController::class, 'downloadCsvTemplateAction']);
    Route::post('/{site}/tree_species/csv', [SiteTreeSpeciesCsvController::class, 'createAction']);
    Route::post('/{site}/tree_species/manual', [SiteTreeSpeciesController::class, 'createAction']);
    Route::delete('/tree_species/{siteTreeSpecies}', [SiteTreeSpeciesController::class, 'deleteAction']);
    Route::get('/tree_species/csv/{siteCsvImport}', [SiteTreeSpeciesCsvController::class, 'readAction']);
    Route::get('/tree_species/csv/{siteCsvImport}/trees', [SiteTreeSpeciesCsvController::class, 'readTreeSpeciesAction']);

    Route::post('/submission', [SiteSubmissionController::class, 'createAction']);
    Route::get('/{site}/submissions', [SiteSubmissionController::class, 'readAllBySiteAction']);
    Route::get('/submission/{siteSubmission}', [SiteSubmissionController::class, 'readAction']);
    Route::patch('/submission/{siteSubmission}', [SiteSubmissionController::class, 'updateAction']);
    Route::patch('/submission/{siteSubmission}/approve', [SiteSubmissionController::class, 'approveAction']);

    Route::post('/submission/disturbance', [SiteSubmissionDisturbanceController::class, 'createAction']);
    Route::put('/submission/disturbance/{siteSubmissionDisturbance}', [SiteSubmissionDisturbanceController::class, 'updateAction']);
    Route::delete('/submission/disturbance/{siteSubmissionDisturbance}', [SiteSubmissionDisturbanceController::class, 'deleteAction']);

    Route::post('/submission/disturbance_information', [SiteSubmissionDisturbanceController::class, 'createDisturbanceInformationAction']);
    Route::put('/submission/disturbance_information/{siteSubmission}', [SiteSubmissionDisturbanceController::class, 'updateDisturbanceInformationAction']);
    Route::delete('/submission/disturbance_information/{siteSubmission}', [SiteSubmissionDisturbanceController::class, 'deleteDisturbanceInformationAction']);

    Route::post('/submission/{siteSubmission}/direct_seeding', [DirectSeedingController::class, 'createAction']);
    Route::delete('/submission/direct_seeding/{directSeeding}', [DirectSeedingController::class, 'deleteAction']);
});

Route::prefix('submission')->group(function () {
    Route::get('/submission_questions', [SubmissionMediaUploadController::class, 'downloadTemplateAction']);
    Route::post('/upload/submission_media', [SubmissionMediaUploadController::class, 'createAction']);
    Route::delete('/upload/submission_media/{submissionMediaUpload}', [SubmissionMediaUploadController::class, 'deleteAction']);

    Route::get('/tree_species/template/csv', [ProgrammeTreeSpeciesCsvController::class, 'downloadCsvTemplateAction']);
    Route::get('/tree_species/template/csv/example', [ProgrammeTreeSpeciesCsvController::class, 'downloadCsvExample']);
});

Route::prefix('exports')->group(function () {
    Route::get('{key}/field_list', [CustomExportController::class, 'availableFieldsAction'])->where('key', 'programme|site|submission|site_submission|control_site|control_site_submission');
    Route::post('custom', [CustomExportController::class, 'generateReportAction']);
});

Route::prefix('ppc')->group(function () {
    Route::prefix('export')->group(function () {
        Route::prefix('programme')->group(function () {
            Route::get('/submissions', ProgrammeSubmissionAdminCsvExportController::class);
            Route::get('/{programme}/shapefiles', ProgrammeShapefileExportController::class);
            Route::get('/{programme}/images', ProgrammeImageExportController::class);
            Route::get('/{programme}/submissions', ProgrammeSubmissionCsvExportController::class);
        });
        Route::prefix('site')->group(function () {
            Route::get('/submissions', SiteSubmissionAdminCsvExportController::class);
            Route::get('/{site}/submissions', SiteSubmissionCsvExportController::class);
        });
    });
});

Route::prefix('terrafund')->group(function () {
    Route::get('/programmes', [TerrafundProgrammeController::class, 'readAllAction']);
    Route::prefix('programmes')->group(function () {
        Route::get('/personal', [TerrafundProgrammeController::class, 'readAllPersonalAction']);
    });
    Route::post('/programme', [TerrafundProgrammeController::class, 'createAction']);
    Route::prefix('programme')->group(function () {
        Route::get('/{terrafundProgramme}', [TerrafundProgrammeController::class, 'readAction']);
        Route::patch('/{terrafundProgramme}', [TerrafundProgrammeController::class, 'updateAction']);
        Route::get('/{terrafundProgramme}/aims', [TerrafundAimsController::class, 'readAction']);
        Route::get('/{terrafundProgramme}/partners', [TerrafundProgrammeController::class, 'readAllPartnersAction']);
        Route::delete('/{terrafundProgramme}/partners/{user}', [TerrafundProgrammeController::class, 'deletePartnerAction']);
        Route::get('/{terrafundProgramme}/sites', [TerrafundProgrammeSitesController::class, 'readAllProgrammeSites']);
        Route::get('/{terrafundProgramme}/all-sites', [TerrafundProgrammeSitesController::class, 'readAllNonPaginatedProgrammeSites']);
        Route::get('/{terrafundProgramme}/site-metrics', [TerrafundProgrammeSitesController::class, 'readAllProgrammeSiteMetrics']);
        Route::get('/{terrafundProgramme}/has_sites', [TerrafundProgrammeSitesController::class, 'checkHasProgrammeSites']);
        Route::get('/{terrafundProgramme}/nurseries', [TerrafundProgrammeNurseriesController::class, 'readAllProgrammeNurseries']);
        Route::get('/{terrafundProgramme}/has_nurseries', [TerrafundProgrammeNurseriesController::class, 'checkHasProgrammeNurseries']);
        Route::post('/{terrafundProgramme}/invite', [TerrafundProgrammeInviteController::class, 'createAction']);
        Route::get('/{terrafundProgramme}/submissions', [TerrafundProgrammeSubmissionController::class, 'readAllProgrammeSubmissions']);
        Route::post('/invite/accept', [TerrafundProgrammeInviteController::class, 'acceptAction']);

        Route::prefix('/submission')->group(function () {
            Route::post('/', [TerrafundProgrammeSubmissionController::class, 'createAction']);
            Route::post('/filter', [TerrafundProgrammeSubmissionController::class, 'filterByDateAction']);
            Route::get('/{terrafundProgrammeSubmission}', [TerrafundProgrammeSubmissionController::class, 'readAction']);
            Route::patch('/{terrafundProgrammeSubmission}', [TerrafundProgrammeSubmissionController::class, 'updateAction']);
        });
    });

    Route::post('/file', [TerrafundFileController::class, 'createAction']);
    Route::post('/file/bulk', [TerrafundFileController::class, 'bulkCreateAction']);

    Route::delete('/file/{terrafundFile}', [TerrafundFileController::class, 'deleteAction']);

    Route::post('/disturbance', [TerrafundDisturbanceController::class, 'createAction']);
    Route::delete('/disturbance/{terrafundDisturbance}', [TerrafundDisturbanceController::class, 'deleteAction']);

    Route::post('/disturbance', [TerrafundDisturbanceController::class, 'createAction']);
    Route::patch('/disturbance/{terrafundDisturbance}', [TerrafundDisturbanceController::class, 'updateAction']);
    Route::delete('/disturbance/{terrafundDisturbance}', [TerrafundDisturbanceController::class, 'deleteAction']);

    Route::post('/tree_species', [TerrafundTreeSpeciesController::class, 'createAction']);
    Route::post('/tree_species_bulk', [TerrafundTreeSpeciesController::class, 'createBulkAction']);
    Route::prefix('tree_species')->group(function () {
        Route::post('/csv', [TerrafundCsvImportController::class, 'createAction']);
        Route::delete('/{treeSpecies}', [TerrafundTreeSpeciesController::class, 'deleteAction']);
        Route::post('/search', [ProgrammeTreeSpeciesController::class, 'searchTreeSpeciesAction']);
    });

    Route::post('/none_tree_species', [TerrafundNoneTreeSpeciesController::class, 'createAction']);
    Route::post('/none_tree_species_bulk', [TerrafundNoneTreeSpeciesController::class, 'createBulkAction']);
    Route::prefix('none_tree_species')->group(function () {
        Route::delete('/{noneTreeSpecies}', [TerrafundNoneTreeSpeciesController::class, 'deleteAction']);
    });

    Route::post('/nursery', [TerrafundNurseryController::class, 'createAction']);
    Route::prefix('nursery')->group(function () {
        Route::get('/tree_species', [TerrafundTreeSpeciesController::class, 'readAllNurseryTreesAction']);
        Route::get('/{terrafundNursery}', [TerrafundNurseryController::class, 'readAction']);
        Route::patch('/{terrafundNursery}', [TerrafundNurseryController::class, 'updateAction']);
        Route::get('/{terrafundNursery}/submissions', [TerrafundNurserySubmissionController::class, 'readAllNurserySubmissions']);
        Route::prefix('/submission')->group(function () {
            Route::get('/due', [TerrafundDueSubmissionController::class, 'readAllDueNurserySubmissionsForUserAction']);
            Route::post('/filter', [TerrafundNurserySubmissionController::class, 'filterByDateAction']);
            Route::post('/', [TerrafundNurserySubmissionController::class, 'createAction']);
            Route::patch('/{terrafundNurserySubmission}', [TerrafundNurserySubmissionController::class, 'updateAction']);
            Route::get('/{terrafundNurserySubmission}', [TerrafundNurserySubmissionController::class, 'readAction']);
        });
        Route::get('/submissions/submitted', [TerrafundDueSubmissionController::class, 'readAllPastNurserySubmissionsForUserAction']);
    });

    Route::post('/site', [TerrafundSiteController::class, 'createAction']);
    Route::prefix('site')->group(function () {
        Route::get('/tree_species', [TerrafundTreeSpeciesController::class, 'readAllSiteTreesAction']);
        Route::get('/land_tenures', [DataController::class, 'readAllTerrafundLandTenuresAction']);
        Route::get('/restoration_methods', [DataController::class, 'readAllTerrafundRestorationMethodsAction']);
        Route::get('/{terrafundSite}', [TerrafundSiteController::class, 'readAction']);
        Route::patch('/{terrafundSite}', [TerrafundSiteController::class, 'updateAction']);
        Route::get('/{terrafundSite}/submissions', [TerrafundSiteSubmissionController::class, 'readAllSiteSubmissions']);
        Route::prefix('/submission')->group(function () {
            Route::post('/', [TerrafundSiteSubmissionController::class, 'createAction']);
            Route::get('/due', [TerrafundDueSubmissionController::class, 'readAllDueSiteSubmissionsForUserAction']);
            Route::post('/filter', [TerrafundSiteSubmissionController::class, 'filterByDateAction']);
            Route::get('/{terrafundSiteSubmission}', [TerrafundSiteSubmissionController::class, 'readAction']);
            Route::patch('/{terrafundSiteSubmission}', [TerrafundSiteSubmissionController::class, 'updateAction']);
        });
        Route::get('/submissions/submitted', [TerrafundDueSubmissionController::class, 'readAllPastSiteSubmissionsForUserAction']);
    });

    Route::prefix('/submission')->group(function () {
        Route::post('/{terrafundDueSubmission}/unable', [TerrafundDueSubmissionController::class, 'unableToReportOnDueSubmissionAction']);
    });
    Route::get('/submissions/due', [TerrafundDueSubmissionController::class, 'readAllDueSubmissionsForUserAction']);

    Route::prefix('my')->group(function () {
        Route::get('/nurseries', [TerrafundNurseryController::class, 'readMyNurseriesAction']);
        Route::get('/sites', [TerrafundSiteController::class, 'readMySitesAction']);
    });

    Route::prefix('export')->group(function () {
        Route::prefix('programme')->group(function () {
            Route::get('/submissions', [TerrafundProgrammeSubmissionCsvExportController::class, 'allProgrammesAction']);
            Route::get('/{terrafundProgramme}/shapefiles', TerrafundProgrammeShapefileExportController::class);
            Route::get('/{terrafundProgramme}/images', TerrafundProgrammeImageExportController::class);
            Route::get('/{terrafundProgramme}/submissions', [TerrafundProgrammeSubmissionCsvExportController::class, 'singleProgrammeAction']);
        });
        Route::prefix('site')->group(function () {
            Route::get('/submissions', TerrafundSiteSubmissionCsvExportController::class);
            Route::get('/{terrafundSite}/submissions', TerrafundSingleSiteSubmissionCsvExportController::class);
            Route::get('/submission/{terrafundSiteSubmission}', TerrafundSiteSingleSubmissionCsvExportController::class);
        });
        Route::prefix('nursery')->group(function () {
            Route::get('/submissions', TerrafundNurserySubmissionCsvExportController::class);
            Route::get('/{terrafundNursery}/submissions', TerrafundSingleNurserySubmissionCsvExportController::class);
            Route::get('/submission/{terrafundNurserySubmission}', TerrafundNurserySingleSubmissionCsvExportController::class);
        });
    });
});

Route::prefix('edit-history')->group(function () {
    Route::post('', [EditHistoryController::class, 'store']);
    Route::get('', [EditHistoryController::class, 'index']);
    Route::put('/approve', [EditHistoryController::class, 'approve']);
    Route::put('/reject', [EditHistoryController::class, 'reject']);
    Route::put('/{uuid}', [EditHistoryController::class, 'update']);
    Route::get('/{uuid}', [EditHistoryController::class, 'view']);
    Route::get('/{type}/{id}', [EditHistoryController::class, 'viewLatestForModel'])->where('type', 'programme|site|terrafund_programme|terrafund_site|terrafund_nursery');
});
