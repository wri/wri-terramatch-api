<?php

namespace App\Helpers;

class RestorationByEcoregionHelper
{
    public static function getCategoryEcoRegion($value, ?bool $isExport = false)
    {
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

        if (isset($value['realm']) && isset($realmMapping[$value['realm']])) {
            $realmName = $realmMapping[$value['realm']];
            $realmValue = 0;

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
