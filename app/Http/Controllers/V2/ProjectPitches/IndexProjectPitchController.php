<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchesCollection;
use App\Models\V2\ProjectPitch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexProjectPitchController extends Controller
{
    public function __invoke(Request $request): ProjectPitchesCollection
    {
        $query = ProjectPitch::whereIn('organisation_id', Auth::user()->all_my_organisations->pluck('uuid'));
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        QueryBuilder::for($query)
            ->selectRaw('
                *,
                (SELECT name FROM funding_programmes WHERE funding_programmes.uuid = funding_programme_id) as funding_programme_name
            ')
            ->allowedFilters([
                'project_name', 'project_objectives', 'project_country',
                'project_county_district', 'restoration_intervention_types',
                'total_hectares', 'total_trees', 'capacity_building_needs',
                'status',
                AllowedFilter::exact('organisation_id'),
                AllowedFilter::scope('has_active_application'),
                AllowedFilter::scope('no_submissions_for_form'),
                AllowedFilter::trashed(),
            ])
            ->allowedSorts([
                'organisation_id', 'project_name', 'project_objectives',
                'project_country', 'project_county_district',
                'restoration_intervention_types', 'total_hectares',
                'total_trees', 'capacity_building_needs',
                'funding_programme_name',
                'created_at', 'updated_at', 'deleted_at',
            ]);

        if ($request->query('search')) {
            $ids = ProjectPitch::search(trim($request->get('search')) ?? '')
                    ->pluck('id')
                    ->toArray();

            if (empty($ids)) {
                return new ProjectPitchesCollection([]);
            }
            $query->whereIn('id', $ids);
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new ProjectPitchesCollection($collection);
    }
}
