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
            data_get($request, 'filter.projectUuid', '')
        );
    }

    private function getCacheParameter($frameworks, $landscapes, $country, $organisations, $projectUuid)
    {
        $frameworkValue = $this->getCacheParameterForFramework($frameworks);
        $landscapeValue = $this->getCacheParameterForLandscapes($landscapes);
        $countryValue = $this->getCacheParameterForCountry($country);
        $organisationValue = $this->getCacheParameterForOrganisations($organisations);
        $projectUuidValue = $this->getCacheParameterForProjectUudid($projectUuid);

        return $frameworkValue .'|'. $landscapeValue .'|'. $countryValue .'|'. $organisationValue .'|'. $projectUuidValue;
    }

    private function getCacheParameterForLandscapes($landscapes)
    {
        return implode(',', $landscapes);
    }

    private function getCacheParameterForOrganisations($organisations)
    {
        return implode(',', $organisations);
    }

    private function getCacheParameterForCountry($country)
    {
        return $country ?? '';
    }

    private function getCacheParameterForFramework($frameworks)
    {
        return implode(',', $frameworks);
    }

    private function getCacheParameterForProjectUudid($projectUuid)
    {
        return $projectUuid ?? '';
    }
}
