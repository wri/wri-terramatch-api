<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware("throttle:30,1")->group(function() {
    Route::post("/auth/login", "AuthController@loginAction");
    Route::get("/auth/resend", "AuthController@resendAction");
    Route::post("/auth/reset", "AuthController@resetAction");
    Route::patch("/auth/change", "AuthController@changeAction");
});

Route::get("/auth/logout", "AuthController@logoutAction");
Route::get("/auth/refresh", "AuthController@refreshAction");
Route::patch("/auth/verify", "AuthController@verifyAction");
Route::get("/auth/me", "AuthController@meAction");

Route::post("/uploads", "UploadsController@createAction");

Route::get("/organisations/{id}/users", "UsersController@readAllByOrganisationAction");
Route::post("/users", "UsersController@createAction");
Route::post("/users/invite", "UsersController@inviteAction");
Route::post("/users/accept", "UsersController@acceptAction");
Route::get("/users/{id}", "UsersController@readAction");
Route::patch("/users/{id}", "UsersController@updateAction");

Route::get("/countries", "DataController@readAllCountriesAction");

Route::post("/organisations", "OrganisationsController@createAction");
Route::get("/organisations/{id}", "OrganisationsController@readAction");
Route::get("/organisations", "OrganisationsController@readAllAction");
Route::patch("/organisations/{id}", "OrganisationsController@updateAction");

Route::get("/organisations/{id}/organisation_versions", "OrganisationVersionsController@readAllByOrganisationAction");
Route::get("/organisation_versions/{id}", "OrganisationVersionsController@readAction");
Route::patch("/organisation_versions/{id}/approve", "OrganisationVersionsController@approveAction");
Route::patch("/organisation_versions/{id}/reject", "OrganisationVersionsController@rejectAction");
Route::delete("/organisation_versions/{id}", "OrganisationVersionsController@deleteAction");

Route::get("/organisation_types", "DataController@readAllOrganisationTypesAction");

Route::get("/organisations/{id}/team_members", "TeamMembersController@readAllByOrganisationAction");
Route::post("/team_members", "TeamMembersController@createAction");
Route::get("/team_members/{id}", "TeamMembersController@readAction");
Route::patch("/team_members/{id}", "TeamMembersController@updateAction");
Route::delete("/team_members/{id}", "TeamMembersController@deleteAction");

Route::get("/organisation_categories", "DataController@readAllOrganisationCategoriesAction");

Route::get("/admins", "AdminsController@readAllAction");
Route::post("/admins/invite", "AdminsController@inviteAction");
Route::post("/admins/accept", "AdminsController@acceptAction");
Route::get("/admins/{id}", "AdminsController@readAction");
Route::patch("/admins/{id}", "AdminsController@updateAction");

Route::get("/document_types", "DataController@readAllDocumentTypesAction");

Route::get("/land_types", "DataController@readAllLandTypesAction");

Route::get("/organisations/{id}/organisation_documents", "OrganisationDocumentsController@readAllByOrganisationAction");
Route::get("/organisations/{id}/organisation_documents/inspect", "OrganisationDocumentsController@inspectByOrganisationAction");
Route::post("/organisation_documents", "OrganisationDocumentsController@createAction");
Route::get("/organisation_documents/{id}", "OrganisationDocumentsController@readAction");
Route::patch("/organisation_documents/{id}", "OrganisationDocumentsController@updateAction");
Route::delete("/organisation_documents/{id}", "OrganisationDocumentsController@deleteAction");

Route::get("/organisation_documents/{id}/organisation_document_versions", "OrganisationDocumentVersionsController@readAllByOrganisationDocumentAction");
Route::get("/organisation_document_versions/{id}", "OrganisationDocumentVersionsController@readAction");
Route::patch("/organisation_document_versions/{id}/approve", "OrganisationDocumentVersionsController@approveAction");
Route::patch("/organisation_document_versions/{id}/reject", "OrganisationDocumentVersionsController@rejectAction");
Route::delete("/organisation_document_versions/{id}", "OrganisationDocumentVersionsController@deleteAction");

Route::get("/organisations/{id}/offers", "OffersController@readAllByOrganisationAction");
Route::post("/offers", "OffersController@createAction");
Route::get("/offers/{id}", "OffersController@readAction");
Route::patch("/offers/{id}", "OffersController@updateAction");
Route::patch("/offers/{id}/complete", "OffersController@completeAction");

Route::get("/land_ownerships", "DataController@readAllLandOwnershipsAction");

Route::get("/offers/{id}/offer_documents", "OfferDocumentsController@readAllByOfferAction");
Route::post("/offer_documents", "OfferDocumentsController@createAction");
Route::get("/offer_documents/{id}", "OfferDocumentsController@readAction");
Route::patch("/offer_documents/{id}", "OfferDocumentsController@updateAction");
Route::delete("/offer_documents/{id}", "OfferDocumentsController@deleteAction");

Route::get("/reporting_levels", "DataController@readAllReportingLevelsAction");

Route::get("/reporting_frequencies", "DataController@readAllReportingFrequenciesAction");

Route::get("/offers/{id}/offer_contacts", "OfferContactsController@readAllByOfferAction");
Route::post("/offer_contacts", "OfferContactsController@createAction");
Route::delete("/offer_contacts/{id}", "OfferContactsController@deleteAction");

Route::get("/sustainable_development_goals", "DataController@readAllSustainableDevelopmentGoalsAction");

Route::get("/continents", "DataController@readAllContinentsAction");

Route::get("/organisations/{id}/pitches", "PitchesController@readAllByOrganisationAction");
Route::get("/organisations/{id}/pitches/inspect", "PitchesController@inspectByOrganisationAction");
Route::post("/pitches", "PitchesController@createAction");
Route::get("/pitches/{id}", "PitchesController@readAction");
Route::patch("/pitches/{id}", "PitchesController@updateAction");
Route::patch("/pitches/{id}/complete", "PitchesController@completeAction");

Route::get("/restoration_goals", "DataController@readAllRestorationGoalsAction");

Route::get("/pitches/{id}/pitch_versions", "PitchVersionsController@readAllByPitchAction");
Route::get("/pitch_versions/{id}", "PitchVersionsController@readAction");
Route::patch("/pitch_versions/{id}/approve", "PitchVersionsController@approveAction");
Route::patch("/pitch_versions/{id}/reject", "PitchVersionsController@rejectAction");
Route::delete("/pitch_versions/{id}", "PitchVersionsController@deleteAction");

Route::get("/revenue_drivers", "DataController@readAllRevenueDriversAction");

Route::get("/pitches/{id}/carbon_certifications", "CarbonCertificationsController@readAllByPitchAction");
Route::get("/pitches/{id}/carbon_certifications/inspect", "CarbonCertificationsController@inspectByPitchAction");
Route::post("/carbon_certifications", "CarbonCertificationsController@createAction");
Route::get("/carbon_certifications/{id}", "CarbonCertificationsController@readAction");
Route::patch("/carbon_certifications/{id}", "CarbonCertificationsController@updateAction");
Route::delete("/carbon_certifications/{id}", "CarbonCertificationsController@deleteAction");

Route::get("/carbon_certifications/{id}/carbon_certification_versions", "CarbonCertificationVersionsController@readAllByCarbonCertificationAction");
Route::get("/carbon_certification_versions/{id}", "CarbonCertificationVersionsController@readAction");
Route::patch("/carbon_certification_versions/{id}/approve", "CarbonCertificationVersionsController@approveAction");
Route::patch("/carbon_certification_versions/{id}/reject", "CarbonCertificationVersionsController@rejectAction");
Route::delete("/carbon_certification_versions/{id}", "CarbonCertificationVersionsController@deleteAction");

Route::get("/restoration_methods", "DataController@readAllRestorationMethodsAction");

Route::get("/pitches/{id}/tree_species", "TreeSpeciesController@readAllByPitchAction");
Route::get("/pitches/{id}/tree_species/inspect", "TreeSpeciesController@inspectByPitchAction");
Route::post("/tree_species", "TreeSpeciesController@createAction");
Route::get("/tree_species/{id}", "TreeSpeciesController@readAction");
Route::patch("/tree_species/{id}", "TreeSpeciesController@updateAction");
Route::delete("/tree_species/{id}", "TreeSpeciesController@deleteAction");

Route::get("/tree_species/{id}/tree_species_versions", "TreeSpeciesVersionsController@readAllByTreeSpeciesAction");
Route::get("/tree_species_versions/{id}", "TreeSpeciesVersionsController@readAction");
Route::patch("/tree_species_versions/{id}/approve", "TreeSpeciesVersionsController@approveAction");
Route::patch("/tree_species_versions/{id}/reject", "TreeSpeciesVersionsController@rejectAction");
Route::delete("/tree_species_versions/{id}", "TreeSpeciesVersionsController@deleteAction");

Route::get("/funding_sources", "DataController@readAllFundingSourcesAction");

Route::get("/pitches/{id}/pitch_documents", "PitchDocumentsController@readAllByPitchAction");
Route::get("/pitches/{id}/pitch_documents/inspect", "PitchDocumentsController@inspectByPitchAction");
Route::post("/pitch_documents", "PitchDocumentsController@createAction");
Route::get("/pitch_documents/{id}", "PitchDocumentsController@readAction");
Route::patch("/pitch_documents/{id}", "PitchDocumentsController@updateAction");
Route::delete("/pitch_documents/{id}", "PitchDocumentsController@deleteAction");

Route::get("/pitch_documents/{id}/pitch_document_versions", "PitchDocumentVersionsController@readAllByPitchDocumentAction");
Route::get("/pitch_document_versions/{id}", "PitchDocumentVersionsController@readAction");
Route::patch("/pitch_document_versions/{id}/approve", "PitchDocumentVersionsController@approveAction");
Route::patch("/pitch_document_versions/{id}/reject", "PitchDocumentVersionsController@rejectAction");
Route::delete("/pitch_document_versions/{id}", "PitchDocumentVersionsController@deleteAction");

Route::get("/tree_species_owners", "DataController@readAllTreeSpeciesOwnersAction");

Route::get("/pitches/{id}/restoration_method_metrics", "RestorationMethodMetricsController@readAllByPitchAction");
Route::get("/pitches/{id}/restoration_method_metrics/inspect", "RestorationMethodMetricsController@inspectByPitchAction");
Route::post("/restoration_method_metrics", "RestorationMethodMetricsController@createAction");
Route::get("/restoration_method_metrics/{id}", "RestorationMethodMetricsController@readAction");
Route::patch("/restoration_method_metrics/{id}", "RestorationMethodMetricsController@updateAction");
Route::delete("/restoration_method_metrics/{id}", "RestorationMethodMetricsController@deleteAction");

Route::get("/restoration_method_metrics/{id}/restoration_method_metric_versions", "RestorationMethodMetricVersionsController@readAllByRestorationMethodMetricAction");
Route::get("/restoration_method_metric_versions/{id}", "RestorationMethodMetricVersionsController@readAction");
Route::patch("/restoration_method_metric_versions/{id}/approve", "RestorationMethodMetricVersionsController@approveAction");
Route::patch("/restoration_method_metric_versions/{id}/reject", "RestorationMethodMetricVersionsController@rejectAction");
Route::delete("/restoration_method_metric_versions/{id}", "RestorationMethodMetricVersionsController@deleteAction");

Route::get("/carbon_certification_types", "DataController@readAllCarbonCertificationTypesAction");

Route::get("/pitches/{id}/pitch_contacts", "PitchContactsController@readAllByPitchAction");
Route::post("/pitch_contacts", "PitchContactsController@createAction");
Route::delete("/pitch_contacts/{id}", "PitchContactsController@deleteAction");

Route::get("/tasks/carbon_certification_versions", "TasksController@readAllCarbonCertificationVersionsAction");
Route::get("/tasks/organisation_document_versions", "TasksController@readAllOrganisationDocumentVersionsAction");
Route::get("/tasks/organisation_versions", "TasksController@readAllOrganisationVersionsAction");
Route::get("/tasks/pitch_document_versions", "TasksController@readAllPitchDocumentVersionsAction");
Route::get("/tasks/pitch_versions", "TasksController@readAllPitchVersionsAction");
Route::get("/tasks/restoration_method_metric_versions", "TasksController@readAllRestorationMethodMetricVersionsAction");
Route::get("/tasks/tree_species_versions", "TasksController@readAllTreeSpeciesVersionsAction");
Route::get("/tasks/matches", "TasksController@readAllMatchesAction");

Route::post("/pitches/search", "PitchesController@searchAction");
Route::post("/offers/search", "OffersController@searchAction");

Route::get("/land_sizes", "DataController@readAllLandSizesAction");

Route::post("/interests", "InterestsController@createAction");
Route::delete("/interests/{id}", "InterestsController@deleteAction");
// Route::get("/interests/initiated", "InterestsController@readAllInitiatedAction");
// Route::get("/interests/received", "InterestsController@readAllReceivedAction");
Route::get("/interests/{type}", "InterestsController@readAllByTypeAction")->where("type", "initiated|received");

Route::get("/matches", "MatchesController@readAllAction");
Route::get("/matches/{id}", "MatchesController@readAction");

Route::get("/notifications", "NotificationsController@readAllAction");
Route::patch("/notifications/{id}/mark", "NotificationsController@markAction");

Route::post("/devices", "DevicesController@createAction");
Route::get("/devices/{id}", "DevicesController@readAction");
Route::get("/devices", "DevicesController@readAllAction");
Route::patch("/devices/{id}", "DevicesController@updateAction");
Route::delete("/devices/{id}", "DevicesController@deleteAction");

Route::get("/reports/organisations", "ReportsController@readAllOrganisationsAction");
Route::get("/reports/organisation_versions", "ReportsController@readAllOrganisationVersionsAction");
Route::get("/reports/approved_organisations", "ReportsController@readAllApprovedOrganisationsAction");
Route::get("/reports/users", "ReportsController@readAllUsersAction");
Route::get("/reports/offers", "ReportsController@readAllOffersAction");
Route::get("/reports/approved_pitches", "ReportsController@readAllApprovedPitchesAction");
Route::get("/reports/rejected_pitches", "ReportsController@readAllRejectedPitchesAction");
Route::get("/reports/interests", "ReportsController@readAllInterestsAction");
Route::get("/reports/funding_amount", "ReportsController@readAllFundingAmountAction");
Route::get("/reports/matches", "ReportsController@readAllMatchesAction");
Route::get("/reports/restored_hectares", "ReportsController@readRestoredHectaresAction");
Route::get("/reports/pitches_benefited_people", "ReportsController@readAllBenefitedPeopleAction");
Route::get("/reports/filter_records", "ReportsController@readAllFiltersRecordsAction");
