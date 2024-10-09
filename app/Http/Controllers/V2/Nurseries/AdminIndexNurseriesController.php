<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\Nurseries\NurseriesCollection;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexNurseriesController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): NurseriesCollection
    {
        $this->authorize('readAll', Nursery::class);

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
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
            ]);

        $this->sort($query, [
            'created_at', '-created_at',
            'start_date', '-start_date',
            'updated_at', '-updated_at',
            'name',
            '-name',
            'project_name',
            '-project_name',
            'organisation_name',
            '-organisation_name',
        ]);

        if (! empty($request->query('search'))) {
            $ids = Nursery::searchNurseries(trim($request->query('search')))->pluck('id')->toArray();
            $query->whereIn('v2_nurseries.id', $ids);
        }

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_nurseries');
        }

        return new NurseriesCollection($this->paginate($query));
    }
}
