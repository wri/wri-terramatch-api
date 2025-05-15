<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class RestorationByEcoregionHelper
{
    public static function getCategoryEcoRegion($value, ?bool $isExport = false)
    {
        Log::info($value);

        // Mapping of realm codes to full realm names
        $realmMapping = [
            'NT' => 'Neotropical',
            'PAL' => 'Palearctic',
            'NA' => 'Nearctic',
            'AF' => 'Afrotropical',
            'IM' => 'Indomalayan',
            'AU' => 'Australasian',
            'OC' => 'Oceanian',
            'AN' => 'Antarctic',
            'AP' => 'Australo-Papuan',
            'AR' => 'Arctic',
        ];

        $formatedValue = [];

        // Check if realm key exists in the input value
        if (isset($value['realm']) && isset($realmMapping[$value['realm']])) {
            $realmName = $realmMapping[$value['realm']];
            $realmValue = 0;

            // Extract the value for the realm, assuming it might be in different formats
            foreach ($value as $key => $val) {
                if ($key !== 'realm') {
                    $realmValue = round((float) $val, 3);

                    break;
                }
            }

            if ($realmValue != 0) {
                $formatedValue[strtolower($realmName)] = $realmValue;
            }
        }

        $result = array_filter($formatedValue, function ($val) {
            return $val !== 0;
        });

        if (empty($result)) {
            return $result;
        }
        if ($isExport) {
            return $result;
        } else {
            return ['data' => $result];
        }
    }
}
