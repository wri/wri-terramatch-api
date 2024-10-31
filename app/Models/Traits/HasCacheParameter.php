<?php

namespace App\Models\Traits;

use App\Models\V2\WorldCountryGeneralized;

trait HasCacheParameter
{
    public function getCacheParameter($frameworks, $landscapes, $country, $organisations)
    {
        $frameworkMask = $this->getCacheParameterForFramework($frameworks);
        $landscapeMask = $this->getCacheParameterForLandscapes($landscapes);
        $countryMask = $this->getCacheParameterForCountry($country);
        $organisationMask = $this->getCacheParameterForOrganisations($organisations);

        return $frameworkMask << 12 | $landscapeMask << 10 | $countryMask << 2 | $organisationMask;
    }

    private function getCacheParameterForLandscapes($landscapes)
    {
        if (empty($landscapes)) {
            return 0;
        }
        if (count($landscapes) === 2) {
            return 3;
        }
        if (count($landscapes) !== 1) {
            throw new \Exception('Invalid number of landscapes');
        }
        return $landscapes[0] == 'terrafund-landscapes' ? 1 : 2;
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
        if (is_null($country)) {
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
