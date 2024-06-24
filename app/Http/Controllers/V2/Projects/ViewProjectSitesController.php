<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Sites\V2SitesCollection;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewProjectSitesController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        $this->authorize('read', $project);

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $sortableColumns = [
            'name', '-name',
            'status', '-status',
            'number_of_trees_planted', '-number_of_trees_planted',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];

        $qry = QueryBuilder::for($project->sites())
            ->selectRaw('
                *,
                (SELECT SUM(amount) FROM v2_tree_species
                    WHERE speciesable_type = ?
                    AND speciesable_id IN (
                        SELECT id FROM v2_site_reports
                        WHERE site_id = v2_sites.id
                    )
                    AND collection = ?) as number_of_trees_planted
            ', [SiteReport::class, 'tree'])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $search = trim($request->query('search'));
            if (is_numeric($search)) {
                $qry->where('v2_sites.ppc_external_id', $search);
            } else {
                $ids = Site::search(trim($request->query('search')))
                    ->get()
                    ->pluck('id')
                    ->toArray();

                if (empty($ids)) {
                    return V2SitesCollection::collection(collect());
                }
                $qry->whereIn('id', $ids);
            }
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return (new V2SitesCollection($collection))
            ->params(['unfiltered_total' => $project->sites()->count()]);
    }
}
