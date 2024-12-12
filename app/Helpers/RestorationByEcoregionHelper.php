<?php

namespace App\Helpers;

class RestorationByEcoregionHelper
{
    public static function getCategoryEcoRegion($value, ?bool $isExport = false)
    {
        $categoriesFromEcoRegion = [
            'australasian' => [
                'Southeast Australia temperate forests',
                'Madeira-Tapajós moist forests',
                'Tocantins/Pindare moist forests',
                'Tapajós-Xingu moist forests',
                'Mato Grosso seasonal forests',
                'Mato Grosso seasonal forests, Xingu-Tocantins-Araguaia moist forests',
                'Bahia coastal forests',
                'Tonle Sap freshwater swamp forests',
            ],
            'afrotropical' => [
                'Sinú Valley dry forests',
                'Santa Marta montane forests',
                'Atlantic mixed forests',
                'Petén-Veracruz moist forests',
                'Central American Atlantic moist forests',
                'Petén-Veracruz moist forests, Central American Atlantic moist forests',
                'Central American montane forests',
                'Central American Atlantic moist forests, Central American montane forests',
                'Northern Acacia-Commiphora bushlands and thickets',
                'Southern Rift montane forest-grassland mosaic',
                'Sierra Madre de Chiapas moist forests',
                'Iberian sclerophyllous and semi-deciduous forests',
                'Northwest Iberian montane forests',
                'Northwestern Congolian lowland forests',
                'Albertine Rift montane forests',
                'Sahelian Acacia savanna',
                'Northern Congolian forest-savanna mosaic',
                'Nigerian lowland forests',
                'West Sudanian savanna',
                'Northern Congolian forest-savanna mosaic, Northwestern Congolian lowland forests',
                'Eastern Guinean forests',
                'Victoria Basin forest-savanna mosaic',
                'Guinean forest-savanna mosaic',
                'East Sudanian savanna',
                'Central Zambezian Miombo woodlands',
                'Ethiopian montane grasslands and woodlands',
                'Central African mangroves',
            ],
            'paleartic' => [
                'southern-zanzibar-inhambane-coastal-forest-mosaic',
            ],
        ];
        $formatedValue = [];
        foreach ($categoriesFromEcoRegion as $category => $values) {
            $formatedValue[$category] = 0;
            foreach ($value as $key => $val) {
                if (in_array($key, $values)) {
                    $formatedValue[$category] = round((float) $val, 3);

                    break;
                }
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
