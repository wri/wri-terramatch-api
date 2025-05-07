<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Applications\ApplicationsCollection;
use App\Models\V2\Forms\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ViewMyApplicationController extends Controller
{
    public function __invoke(Request $request): ApplicationsCollection
    {
        $this->authorize('viewOnlyMine', Application::class);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        $orgUuids = collect(Auth::user()->all_my_organisations)->pluck('uuid')->unique()->toArray();

        $submissionUuids = Application::whereIn('applications.organisation_uuid', $orgUuids)
            ->selectRaw('(SELECT uuid FROM form_submissions WHERE form_submissions.application_id = applications.id LIMIT 1) as form_submission_uuid')
            ->pluck('form_submission_uuid')
            ->toArray();

        $query = Application::whereIn('applications.organisation_uuid', $orgUuids);

        QueryBuilder::for($query)
            ->join('form_submissions', function ($join) use ($submissionUuids) {
                $join->on('applications.id', '=', 'form_submissions.application_id');
                $join->whereIn('form_submissions.uuid', $submissionUuids);
            })
            ->join('stages', 'form_submissions.stage_uuid', 'stages.uuid')
            ->selectRaw('
                *,
                (SELECT status FROM form_submissions WHERE form_submissions.application_id = applications.id ORDER BY created_at DESC LIMIT 1) as form_submission_status,
                (SELECT name FROM funding_programmes WHERE funding_programmes.uuid = funding_programme_id) as funding_programme_name,
                (SELECT name FROM stages WHERE stages.uuid = form_submissions.stage_uuid) as stage_name,
                applications.*
            ')
            ->allowedSorts([
                'funding_programme_name', '-funding_programme_name',
                'stage_name', '-stage_name',
                'form_submission_status', '-form_submission_status',
            ])
            ->allowedFilters([
                AllowedFilter::exact('funding_programme_uuid')
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new ApplicationsCollection($collection);
    }
}
