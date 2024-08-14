<?php

namespace App\Http\Controllers\V2\ProjectReports;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\IsAdminIndex;
use App\Http\Resources\V2\ProjectReports\ProjectReportsCollection;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexProjectReportsController extends Controller
{
    use IsAdminIndex;

    public function __invoke(Request $request): ProjectReportsCollection
    {
        $this->authorize('readAll', ProjectReport::class);

        $query = QueryBuilder::for(ProjectReport::class)
             ->join('v2_projects', function ($join) {
                 $join->on('v2_project_reports.project_id', '=', 'v2_projects.id');
             })
             ->selectRaw('
                v2_project_reports.*,
                (SELECT name FROM organisations WHERE organisations.id = v2_projects.organisation_id) as organisation_name
            ')
             ->allowedFilters([
                 AllowedFilter::scope('project_uuid', 'projectUuid'),
                 AllowedFilter::scope('country'),
                 AllowedFilter::exact('status'),
                 AllowedFilter::exact('update_request_status'),
                 AllowedFilter::exact('framework_key'),
             ]);

        $this->sort($query, [
            'created_at', '-created_at',
            'updated_at', '-updated_at',
            'title', '-title',
            'framework_key', '-framework_key',
            'organisation_name', '-organisation_name',
            'due_at', '-due_at',
            'created_at', '-created_at',
            'status', '-status',
        ]);

        if (! empty($request->query('search'))) {
            $ids = ProjectReport::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $query->whereIn('v2_project_reports.id', $ids);
        }

        $user = User::find(Auth::user()->id);
        if ($user->primaryRole?->name == 'project-manager') {
            $query->whereIn('project_id', $user->managedProjects()->select('v2_projects.id'));
        } else {
            $this->isolateAuthorizedFrameworks($query, 'v2_project_reports');
        }

        return new ProjectReportsCollection($this->paginate($query));
    }
}
