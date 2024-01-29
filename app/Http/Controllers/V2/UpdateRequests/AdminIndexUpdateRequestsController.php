<?php

namespace App\Http\Controllers\V2\UpdateRequests;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestsCollection;
use App\Models\Framework;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexUpdateRequestsController extends Controller
{
    public function __invoke(Request $request): UpdateRequestsCollection
    {
        $this->authorize('readAll', UpdateRequest::class);
        $user = Auth::user();

        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'project_name',
            '-project_name',
            'organisation_name',
            '-organisation_name',
        ];

        $query = QueryBuilder::for(UpdateRequest::class)
            ->selectRaw(
                '
                v2_update_requests.*,
                (SELECT name FROM v2_projects WHERE v2_projects.id = project_id) as project_name,
                (SELECT name FROM organisations WHERE organisations.id = (SELECT organisation_id FROM v2_projects WHERE v2_projects.id = project_id)) as organisation_name
            '
            )->allowedFilters([
                AllowedFilter::scope('organisation'),
                AllowedFilter::exact('project', 'project_id'),
                AllowedFilter::exact('framework', 'framework_key'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

        $frameworks = Framework::all();

        $frameworkNamesWithPref = $frameworks->map(function ($framework) {
            return 'framework-' . $framework->slug;
        })->toArray();

        $frameworkNames = $frameworks->map(function ($framework) {
            return $framework->slug;
        })->toArray();

        if (! $user->hasAllPermissions($frameworkNamesWithPref)) {
            $query->where(function ($query) use ($frameworkNames, $user) {
                foreach ($frameworkNames as $framework) {
                    $frameworkPermission = 'framework-' . $framework;
                    if ($user->hasPermissionTo($frameworkPermission)) {
                        $query->orWhere('framework_key', $framework);
                    }
                }
            });
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new UpdateRequestsCollection($collection);
    }
}
