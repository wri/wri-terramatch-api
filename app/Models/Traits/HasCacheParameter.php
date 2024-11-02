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
            data_get($request, 'filter.organisations.type', []),
        );
    }

    private function getCacheParameter($frameworks, $landscapes, $country, $organisations)
    {
        $frameworkValue = $this->getCacheParameterForFramework($frameworks);
        $landscapeValue = $this->getCacheParameterForLandscapes($landscapes);
        $countryValue = $this->getCacheParameterForCountry($country);
        $organisationValue = $this->getCacheParameterForOrganisations($organisations);

        return $frameworkValue .'|'. $landscapeValue .'|'. $countryValue .'|'. $organisationValue;
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
}
