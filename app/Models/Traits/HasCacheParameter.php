<?php

namespace App\Models\Traits;

use App\Models\V2\WorldCountryGeneralized;

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
        $frameworkMask = $this->getCacheParameterForFramework($frameworks);
        $landscapeMask = $this->getCacheParameterForLandscapes($landscapes);
        $countryMask = $this->getCacheParameterForCountry($country);
        $organisationMask = $this->getCacheParameterForOrganisations($organisations);

        return $frameworkMask << 13 | $landscapeMask << 10 | $countryMask << 2 | $organisationMask;
    }

    private function getCacheParameterForLandscapes($landscapes)
    {
        if (empty($landscapes)) {
            return 0;
        }
        if (count($landscapes) === 3) {
            return 7;
        }
        if (count($landscapes) > 3) {
            throw new \Exception('Invalid number of landscapes');
        }
        if (count($landscapes) === 1) {
            return $this->getValueLandscape($landscapes[0]);
        }

        if (count($landscapes) === 2) {
            $first = $this->getValueLandscape($landscapes[0]);
            $second = $this->getValueLandscape($landscapes[1]);

            return $first + $second;
        }

        return $landscapes[0] == 'terrafund-landscapes' ? 1 : 2;
    }

    private function getValueLandscape($landscape)
    {
        return ($landscape == 'ghana_cocoa_belt') ? 1 : ($landscape == 'lake_kivu_rusizi_river_basin' ? 2 : 4);
    }

    private function getCacheParameterForOrganisations($organisations)
    {
        if (empty($organisations)) {
            return 0;
        }
        if (count($organisations) === 2) {
            return 3;
        }
        if (count($organisations) !== 1) {
            throw new \Exception('Invalid number of organisations');
        }

        return $organisations[0] == 'non-profit-organization' ? 1 : 2;
    }

    private function getCacheParameterForCountry($country)
    {
        if (is_null($country) || empty($country)) {
            return 0;
        }

        return WorldCountryGeneralized::where('iso', $country)->first()->OGR_FID - 252 + 1;
    }

    private function getCacheParameterForFramework($frameworks)
    {
        if (empty($frameworks)) {
            return 0;
        }
        if (count($frameworks) === 2) {
            return 3;
        }
        if (count($frameworks) !== 1) {
            throw new \Exception('Invalid number of frameworks');
        }

        return $frameworks[0] == 'terrafund-landscapes' ? 1 : 2;
    }
}
