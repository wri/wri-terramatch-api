<?php

namespace App\Models\Traits;

trait HasCacheParameter
{
    public function getParametersFromRequest($request)
    {
        return $this->getCacheParameter(
            data_get($request, 'filter.programmes', []),
            data_get($request, 'filter.landscapes', []),
            data_get($request, 'filter.country', ''),
            data_get($request, 'filter.organisationType', []),
            data_get($request, 'filter.cohort', ''),
            data_get($request, 'filter.projectUuid', '')
        );
    }

    private function getCacheParameter($frameworks, $landscapes, $country, $organisations, $cohort, $projectUuid)
    {
        $frameworkValue = $this->getCacheParameterForFramework($frameworks);
        $landscapeValue = $this->getCacheParameterForLandscapes($landscapes);
        $countryValue = $this->getCacheParameterForCountry($country);
        $organisationValue = $this->getCacheParameterForOrganisations($organisations);
        $cohortValue = $this->getCacheParameterForCohort($cohort);
        $projectUuidValue = $this->getCacheParameterForProjectUudid($projectUuid);

        return $frameworkValue .'|'. $landscapeValue .'|'. $countryValue .'|'. $organisationValue .'|'. $cohortValue .'|'. $projectUuidValue;
    }

    private function getCacheParameterForLandscapes($landscapes)
    {
        if (empty($landscapes)) {
            return '';
        }

        $sortedLandscapes = is_array($landscapes) ? $landscapes : [$landscapes];

        sort($sortedLandscapes);

        return implode(',', $sortedLandscapes);
    }

    private function getCacheParameterForOrganisations($organisations)
    {
        if (empty($organisations)) {
            return '';
        }

        $sortedOrganisations = is_array($organisations) ? $organisations : [$organisations];

        sort($sortedOrganisations);

        return implode(',', $sortedOrganisations);
    }

    private function getCacheParameterForCountry($country)
    {
        return $country ?? '';
    }

    private function getCacheParameterForFramework($frameworks)
    {
        if (empty($frameworks)) {
            return '';
        }

        $sortedFrameworks = is_array($frameworks) ? $frameworks : [$frameworks];

        sort($sortedFrameworks);

        return implode(',', $sortedFrameworks);
    }

    private function getCacheParameterForProjectUudid($projectUuid)
    {
        return $projectUuid ?? '';
    }

    private function getCacheParameterForCohort($cohort)
    {
        return $cohort ?? '';
    }
}
