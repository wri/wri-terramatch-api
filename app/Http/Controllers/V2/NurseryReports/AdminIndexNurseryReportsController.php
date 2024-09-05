<?php

namespace App\Http\Controllers\V2\NurseryReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\NurseryReports\NurseryReportsCollection;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexNurseryReportsController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): NurseryReportsCollection
    {
        $this->authorize('readAll', NurseryReport::class);

        $query = QueryBuilder::for(NurseryReport::class)
            ->join('v2_nurseries', function ($join) {
                $join->on('v2_nursery_reports.nursery_id', '=', 'v2_nurseries.id');
            })
            ->join('v2_projects', function ($join) {
                $join->on('v2_nurseries.project_id', '=', 'v2_projects.id');
            })
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->selectRaw('
                v2_nursery_reports.*,
                v2_projects.name as project_name,
                (SELECT name FROM organisations WHERE organisations.id = v2_projects.organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::scope('project_uuid', 'projectUuid'),
                AllowedFilter::scope('nursery_uuid', 'nurseryUuid'),
                AllowedFilter::scope('country'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('update_request_status'),
                AllowedFilter::exact('framework_key'),
                AllowedFilter::scope('organisation_uuid', 'organisationUuid'),
            ]);

        $this->sort($query, [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'project_name', '-project_name',
            'title', '-title',
            'framework_key', '-framework_key',
            'organisation_name', '-organisation_name',
            'due_at', '-due_at',
            'status', '-status',
        ]);

        if (! empty($request->query('search'))) {
            $ids = NurseryReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $query->whereIn('v2_nursery_reports.id', $ids);
        }

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('v2_nurseries.project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_nursery_reports');
        }

        return new NurseryReportsCollection($this->paginate($query));
    }
}
