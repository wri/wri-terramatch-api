<?php

namespace App\Helpers;

class RestorationByEcoregionHelper
{
    public static function getCategoryEcoRegion($value, ?bool $isExport = false)
    {
        $categoriesFromEcoRegion = [
            'australasian' => [
                'Southeast Australia temperate forests',
                'Tocantins/Pindare moist forests',
                'Tapajós-Xingu moist forests',
                'Mato Grosso seasonal forests',
                'Mato Grosso seasonal forests, Xingu-Tocantins-Araguaia moist forests',
                'Bahia coastal forests',
                'Southern Miombo woodlands',
                'Palawan rain forests',
            ],
            'afrotropical' => [
                'Atlantic mixed forests',
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
                'Southern Acacia-Commiphora bushlands and thickets',
                'East African montane forests',
                'Eastern Arc forests',
                'Guinean mangroves',
                'Eastern Zimbabwe montane forest-grassland mosaic',
                'Somali Acacia-Commiphora bushlands and thickets',
                'Ethiopian montane forests',
                'Inner Niger Delta flooded savanna',
                'Western Guinean lowland forests',
                'Eastern Miombo woodlands',
                'Ethiopian montane forests, Ethiopian montane grasslands and woodlands',
                'Cross-Sanaga-Bioko coastal forests',
                'Zambezian and Mopane woodlands',
                'Madagascar lowland forests',
                'Madagascar subhumid forests',
                'Southern Congolian forest-savanna mosaic',
                'East African montane forests',
                'East African montane forests, Northern Acacia-Commiphora bushlands and thickets',
                'Albertine Rift montane forests, Lake',
            ],
            'paleartic' => [
                'Southwest Iberian Mediterranean sclerophyllous and mixed forests',
                'Narmada Valley dry deciduous forests',
                'East African montane moorlands',
                'Cameroonian Highlands forests',
                'Celtic broadleaf forests',
                'Atlantic Coast restingas',
                'Gulf of Oman desert and semi-desert',
            ],
            'neotropical' => [
                'Sinú Valley dry forests',
                'Santa Marta montane forests',
                'Petén-Veracruz moist forests',
                'Central American Atlantic moist forests',
                'Petén-Veracruz moist forests, Central American Atlantic moist forests',
                'Central American montane forests',
                'Central American Atlantic moist forests, Central American montane forests',
                'Cross-Niger transition forests',
                'Atlantic Coast restingas',
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
