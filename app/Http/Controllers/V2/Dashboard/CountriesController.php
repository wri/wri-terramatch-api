<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\CountriesResource;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;

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
        $projectsCountrieslug = Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
            })->pluck('country');
        $countryId = FormOptionList::where('key', 'countries')->value('id');
        $countries = FormOptionListOption::where('form_option_list_id', $countryId)
            ->orderBy('label')
            ->select('id', 'label', 'slug')
            ->get();
        $countriesResponse = [];
        foreach ($countries as $country) {
            if ($this->verifyProjects($country->slug, $projectsCountrieslug)) {
                $countriesResponse[] = [
                    'country_slug' => $country->slug,
                    'id' => $country->id,
                    'data' => (object) [
                        'label' => $country->label,
                        'icon' => '/flags/' . $country->label . '.svg',
                    ],
                ];
            }
        }

        return $countriesResponse;
    }

    public function verifyProjects($country, $projectsSlug)
    {
        $projects = $projectsSlug->contains($country);
        if ($projects) {
            return true;
        } else {
            return false;
        }

    }
}
