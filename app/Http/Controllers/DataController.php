<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * This class holds all the endpoints which return static data. The data comes
 * from config files and are used to power dropdowns in the app and website.
 */
class DataController extends Controller
{
    public function readAllCountriesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $countries = [];
        foreach (config('data.continents_countries') as $continent => $continentsCountries) {
            foreach ($continentsCountries as $name => $code) {
                $countries[] = (object) [
                    'name' => $name,
                    'code' => $code,
                    'continent' => $continent,
                ];
            }
        }
        $countries = ArrayHelper::sortBy($countries, 'name', ArrayHelper::ASC);

        return JsonResponseHelper::success($countries, 200);
    }

    public function readAllOrganisationTypesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $organisationTypes = [];
        foreach (config('data.organisation_types') as $key => $value) {
            $organisationTypes[] = (object) [
                'name' => $key,
                'type' => $value,
            ];
        }

        return JsonResponseHelper::success($organisationTypes, 200);
    }

    public function readAllOrganisationCategoriesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $organisationCategories = [];
        foreach (config('data.organisation_categories') as $key => $value) {
            $organisationCategories[] = (object) [
                'name' => $key,
                'category' => $value,
            ];
        }

        return JsonResponseHelper::success($organisationCategories, 200);
    }

    public function readAllDocumentTypesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $documentTypes = [];
        foreach (config('data.document_types') as $key => $value) {
            $documentTypes[] = (object) [
                'name' => $key,
                'type' => $value,
            ];
        }

        return JsonResponseHelper::success($documentTypes, 200);
    }

    public function readAllLandTypesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $landTypes = [];
        foreach (config('data.land_types') as $key => $value) {
            $landTypes[] = (object) [
                'name' => $key,
                'type' => $value,
            ];
        }

        return JsonResponseHelper::success($landTypes, 200);
    }

    public function readAllLandOwnershipsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $landOwnerships = [];
        foreach (config('data.land_ownerships') as $key => $value) {
            $landOwnerships[] = (object) [
                'name' => $key,
                'ownership' => $value,
            ];
        }

        return JsonResponseHelper::success($landOwnerships, 200);
    }

    public function readAllRestorationMethodsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $restorationMethods = [];
        foreach (config('data.restoration_methods') as $key => $value) {
            $restorationMethods[] = (object) [
                'name' => $key,
                'method' => $value,
            ];
        }

        return JsonResponseHelper::success($restorationMethods, 200);
    }

    public function readAllRestorationGoalsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $restorationGoals = [];
        foreach (config('data.restoration_goals') as $key => $value) {
            $restorationGoals[] = (object) [
                'name' => $key,
                'goal' => $value,
            ];
        }

        return JsonResponseHelper::success($restorationGoals, 200);
    }

    public function readAllFundingSourcesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $fundingSources = [];
        foreach (config('data.funding_sources') as $key => $value) {
            $fundingSources[] = (object) [
                'name' => $key,
                'source' => $value,
            ];
        }

        return JsonResponseHelper::success($fundingSources, 200);
    }

    public function readAllSustainableDevelopmentGoalsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $sustainableDevelopmentGoals = [];
        foreach (config('data.sustainable_development_goals') as $key => $value) {
            $sustainableDevelopmentGoals[] = (object) [
                'name' => $key,
                'goal' => $value,
            ];
        }

        return JsonResponseHelper::success($sustainableDevelopmentGoals, 200);
    }

    public function readAllContinentsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $continents = [];
        foreach (config('data.continents') as $key => $value) {
            $continents[] = (object) [
                'name' => $key,
                'continent' => $value,
            ];
        }

        return JsonResponseHelper::success($continents, 200);
    }

    public function readAllReportingFrequenciesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $reportingFrequencies = [];
        foreach (config('data.reporting_frequencies') as $key => $value) {
            $reportingFrequencies[] = (object) [
                'name' => $key,
                'frequency' => $value,
            ];
        }

        return JsonResponseHelper::success($reportingFrequencies, 200);
    }

    public function readAllReportingLevelsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $reportingLevels = [];
        foreach (config('data.reporting_levels') as $key => $value) {
            $reportingLevels[] = (object) [
                'name' => $key,
                'level' => $value,
            ];
        }

        return JsonResponseHelper::success($reportingLevels, 200);
    }

    public function readAllRevenueDriversAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $revenueDrivers = [];
        foreach (config('data.revenue_drivers') as $key => $value) {
            $revenueDrivers[] = (object) [
                'name' => $key,
                'driver' => $value,
            ];
        }

        return JsonResponseHelper::success($revenueDrivers, 200);
    }

    public function readAllCarbonCertificationTypesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $carbonCertificationTypes = [];
        foreach (config('data.carbon_certification_types') as $key => $value) {
            $carbonCertificationTypes[] = (object) [
                'name' => $key,
                'type' => $value,
            ];
        }

        return JsonResponseHelper::success($carbonCertificationTypes, 200);
    }

    public function readAllLandSizesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $landSizes = [];
        foreach (config('data.land_sizes') as $key => $value) {
            $landSizes[] = (object) [
                'name' => $key,
                'size' => $value,
            ];
        }

        return JsonResponseHelper::success($landSizes, 200);
    }

    public function readAllRejectedReasonsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $rejectedReasons = [];
        foreach (config('data.rejected_reasons') as $key => $value) {
            $rejectedReasons[] = (object) [
                'name' => $key,
                'reason' => $value,
            ];
        }

        return JsonResponseHelper::success($rejectedReasons, 200);
    }

    public function readAllFundingBracketsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $fundingBrackets = [];
        foreach (config('data.funding_brackets') as $key => $value) {
            $fundingBrackets[] = (object) [
                'name' => $key,
                'bracket' => $value,
            ];
        }

        return JsonResponseHelper::success($fundingBrackets, 200);
    }

    public function readAllVisibilitiesAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $visibilities = [];
        foreach (config('data.visibilities') as $key => $value) {
            $visibilities[] = (object) [
                'name' => $key,
                'visibility' => $value,
            ];
        }

        return JsonResponseHelper::success($visibilities, 200);
    }

    public function readAllTerrafundLandTenuresAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $landTenures = [];
        foreach (config('data.terrafund.site.land_tenures') as $key => $value) {
            $landTenures[] = (object) [
                'name' => $key,
                'key' => $value,
            ];
        }

        return JsonResponseHelper::success($landTenures, 200);
    }

    public function readAllTerrafundRestorationMethodsAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $restorationMethods = [];
        foreach (config('data.terrafund.site.restoration_methods') as $key => $value) {
            $restorationMethods[] = (object) [
                'name' => $key,
                'key' => $value,
            ];
        }

        return JsonResponseHelper::success($restorationMethods, 200);
    }
}
