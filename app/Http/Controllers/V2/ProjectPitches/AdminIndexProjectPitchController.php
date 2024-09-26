<?php

namespace App\Http\Controllers\V2\ProjectPitches;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ProjectPitches\ProjectPitchesCollection;
use App\Models\V2\ProjectPitch;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexProjectPitchController extends Controller
{
    public function __invoke(Request $request): ProjectPitchesCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $sortableColumns = [
            'organisation_id', '-organisation_id', 'project_name', '-project_name', 'project_objectives', '-project_objectives',
            'project_country',  '-project_country', '-project_county_district','project_county_district',
            'restoration_intervention_types', '-restoration_intervention_types', 'total_hectares', '-total_hectares',
            'total_trees', '-total_trees','capacity_building_needs', '-capacity_building_needs',
            'created_at', '-created_at','updated_at','-updated_at', 'deleted_at', '-deleted_at',
        ];

        $query = QueryBuilder::for(ProjectPitch::class)
            ->allowedFilters([
                'project_name', 'project_objectives', 'project_country',
                'project_county_district', 'restoration_intervention_types',
                'total_hectares', 'total_trees', 'capacity_building_needs',
                'status',
                AllowedFilter::exact('organisation_id'),
                AllowedFilter::trashed(),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

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
