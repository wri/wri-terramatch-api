<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseriesCollection;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexNurseriesController extends Controller
{
    public function __invoke(Request $request): NurseriesCollection
    {
        $this->authorize('readAll', Nursery::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'start_date', '-start_date',
            'updated_at', '-updated_at',
            'name',
            '-name',
            'project_name',
            '-project_name',
            'organisation_name',
            '-organisation_name',
        ];

        $query = QueryBuilder::for(Nursery::class)
            ->selectRaw(
                '
                v2_nurseries.*,
                (SELECT name FROM v2_projects WHERE v2_projects.id = project_id) as project_name,
                (SELECT name FROM organisations WHERE organisations.id = (SELECT organisation_id FROM v2_projects WHERE v2_projects.id = project_id)) as organisation_name
            '
            )->allowedFilters([
                AllowedFilter::scope('country'),
                AllowedFilter::scope('organisation_uuid', 'organisationUuid'),
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::exact('framework', 'framework_key'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = Nursery::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $query->whereIn('v2_nurseries.id', $ids);
        }

        if (! $user->hasAllPermissions(['framework-ppc', 'framework-terrafund'])) {
            if ($user->hasPermissionTo('framework-terrafund')) {
                $query->where('framework_key', 'terrafund');
            } elseif ($user->hasPermissionTo('framework-ppc')) {
                $query->where('framework_key', 'ppc');
            }
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new NurseriesCollection($collection);
    }
}
