<?php

namespace App\Http\Controllers;

use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use Exception;

/**
 * This class holds all the endpoints which return static data. The data comes
 * from config files and are used to power dropdowns in the app and website.
 */
class DataController extends Controller
{
    private $jsonResponseFactory = null;
    private $config = null;

    public function __construct(JsonResponseFactory $jsonResponseFactory, Config $config)
    {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->config = $config;
    }

    public function readAllCountriesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $countries = [];
        foreach ($this->config->get("data.countries") as $key => $value) {
            $countries[] = (object) [
                "name" => $key,
                "code" => $value
            ];
        }
        return $this->jsonResponseFactory->success($countries, 200);
    }

    public function readAllOrganisationTypesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $organisationTypes = [];
        foreach ($this->config->get("data.organisation_types") as $key => $value) {
            $organisationTypes[] = (object) [
                "name" => $key,
                "type" => $value
            ];
        }
        return $this->jsonResponseFactory->success($organisationTypes, 200);
    }

    public function readAllOrganisationCategoriesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $organisationCategories = [];
        foreach ($this->config->get("data.organisation_categories") as $key => $value) {
            $organisationCategories[] = (object) [
                "name" => $key,
                "category" => $value
            ];
        }
        return $this->jsonResponseFactory->success($organisationCategories, 200);
    }

    public function readAllDocumentTypesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $documentTypes = [];
        foreach ($this->config->get("data.document_types") as $key => $value) {
            $documentTypes[] = (object) [
                "name" => $key,
                "type" => $value
            ];
        }
        return $this->jsonResponseFactory->success($documentTypes, 200);
    }

    public function readAllLandTypesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $landTypes = [];
        foreach ($this->config->get("data.land_types") as $key => $value) {
            $landTypes[] = (object) [
                "name" => $key,
                "type" => $value
            ];
        }
        return $this->jsonResponseFactory->success($landTypes, 200);
    }

    public function readAllLandOwnershipsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $landOwnerships = [];
        foreach ($this->config->get("data.land_ownerships") as $key => $value) {
            $landOwnerships[] = (object) [
                "name" => $key,
                "ownership" => $value
            ];
        }
        return $this->jsonResponseFactory->success($landOwnerships, 200);
    }

    public function readAllRestorationMethodsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $restorationMethods = [];
        foreach ($this->config->get("data.restoration_methods") as $key => $value) {
            $restorationMethods[] = (object) [
                "name" => $key,
                "method" => $value
            ];
        }
        return $this->jsonResponseFactory->success($restorationMethods, 200);
    }

    public function readAllRestorationGoalsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $restorationGoals = [];
        foreach ($this->config->get("data.restoration_goals") as $key => $value) {
            $restorationGoals[] = (object) [
                "name" => $key,
                "goal" => $value
            ];
        }
        return $this->jsonResponseFactory->success($restorationGoals, 200);
    }

    public function readAllFundingSourcesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $fundingSources = [];
        foreach ($this->config->get("data.funding_sources") as $key => $value) {
            $fundingSources[] = (object) [
                "name" => $key,
                "source" => $value
            ];
        }
        return $this->jsonResponseFactory->success($fundingSources, 200);
    }

    public function readAllSustainableDevelopmentGoalsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $sustainableDevelopmentGoals = [];
        foreach ($this->config->get("data.sustainable_development_goals") as $key => $value) {
            $sustainableDevelopmentGoals[] = (object) [
                "name" => $key,
                "goal" => $value
            ];
        }
        return $this->jsonResponseFactory->success($sustainableDevelopmentGoals, 200);
    }

    public function readAllContinentsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $continents = [];
        foreach ($this->config->get("data.continents") as $key => $value) {
            $continents[] = (object) [
                "name" => $key,
                "continent" => $value
            ];
        }
        return $this->jsonResponseFactory->success($continents, 200);
    }

    public function readAllReportingFrequenciesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $reportingFrequencies = [];
        foreach ($this->config->get("data.reporting_frequencies") as $key => $value) {
            $reportingFrequencies[] = (object) [
                "name" => $key,
                "frequency" => $value
            ];
        }
        return $this->jsonResponseFactory->success($reportingFrequencies, 200);
    }

    public function readAllReportingLevelsAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $reportingLevels = [];
        foreach ($this->config->get("data.reporting_levels") as $key => $value) {
            $reportingLevels[] = (object) [
                "name" => $key,
                "level" => $value
            ];
        }
        return $this->jsonResponseFactory->success($reportingLevels, 200);
    }

    public function readAllRevenueDriversAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $revenueDrivers = [];
        foreach ($this->config->get("data.revenue_drivers") as $key => $value) {
            $revenueDrivers[] = (object) [
                "name" => $key,
                "driver" => $value
            ];
        }
        return $this->jsonResponseFactory->success($revenueDrivers, 200);
    }

    public function readAllCarbonCertificationTypesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $carbonCertificationTypes = [];
        foreach ($this->config->get("data.carbon_certification_types") as $key => $value) {
            $carbonCertificationTypes[] = (object) [
                "name" => $key,
                "type" => $value
            ];
        }
        return $this->jsonResponseFactory->success($carbonCertificationTypes, 200);
    }

    public function readAllTreeSpeciesOwnersAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $treeSpeciesOwners = [];
        foreach ($this->config->get("data.tree_species_owners") as $key => $value) {
            $treeSpeciesOwners[] = (object) [
                "name" => $key,
                "owner" => $value
            ];
        }
        return $this->jsonResponseFactory->success($treeSpeciesOwners, 200);
    }

    public function readAllLandSizesAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        $landSizes = [];
        foreach ($this->config->get("data.land_sizes") as $key => $value) {
            $landSizes[] = (object) [
                "name" => $key,
                "size" => $value
            ];
        }
        return $this->jsonResponseFactory->success($landSizes, 200);
    }
}
