<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseriesCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewProjectNurseriesController extends Controller
{
    public function __invoke(Request $request, Project $project): NurseriesCollection
    {
        $this->authorize('read', $project);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $sortableColumns = [
            'name', '-name',
            'status', '-status',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];

        $qry = QueryBuilder::for($project->nurseries())
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = Nursery::search(trim($request->query('search')))->pluck('id')->toArray();

            if (empty($ids)) {
                return new NurseriesCollection(collect());
            }

            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return (new NurseriesCollection($collection))
            ->params(['unfiltered_total' => $project->nurseries()->count()]);
    }
}
