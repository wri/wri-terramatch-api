<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class CountriesController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'data' => $this->getAllCountries($request),
        ]);
    }

    public function getAllCountries($request)
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
            if ($request->input('country')) {
                $countriesResponse[] = [
                    'country_slug' => $country->slug,
                    'id' => $country->id,
                    'data' => (object) [
                        'label' => $country->label,
                        'icon' => '/flags/' . strtolower($country->slug) . '.svg',
                    ],
                ];
            } elseif ($this->hasProjectsInCountry($country->slug, $projectsCountrieslug)) {
                $countriesResponse[] = [
                    'country_slug' => $country->slug,
                    'id' => $country->id,
                    'data' => (object) [
                        'label' => $country->label,
                        'icon' => '/flags/' . strtolower($country->slug) . '.svg',
                    ],
                ];
            }
        }

        return $countriesResponse;
    }

    public function hasProjectsInCountry($country, $projectsSlug)
    {
        return $projectsSlug->contains($country);
    }
}
