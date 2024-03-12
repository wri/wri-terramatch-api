<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\CountriesResource;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;

class CountriesController extends Controller
{
    public function __invoke(): CountriesResource
    {
        $response = (object) [
            'data' => $this->getAllCountries(),
        ];

        return new CountriesResource($response);
    }

    public function getAllCountries()
    {
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $countries = FormOptionListOption::where('form_option_list_id', $countryId)
            ->orderBy('label')
            ->get();
        $countriesResponse = [];
        foreach ($countries as $country) {
            $countriesResponse[] = [
                'country_slug' => $country->slug,
                'id' => $country->id,
                'data' => (object) [
                    'label' => $country->label,
                    'icon' => '/flags/' . $country->label . '.svg',
                ],
            ];
        }

        return $countriesResponse;
    }
}
