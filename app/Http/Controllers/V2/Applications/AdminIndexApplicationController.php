<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Applications\ApplicationsCollection;
use App\Models\V2\Forms\Application;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminIndexApplicationController extends Controller
{
    public function __invoke(Request $request): ApplicationsCollection
    {
        $this->authorize('readAll', Application::class);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'organisation_name', '-organisation_name',
            'organisation_uuid', '-organisation_uuid',
            'funding_programme_name', '-funding_programme_name',
            'updated_at', '-updated_at',
        ];

        $qry = QueryBuilder::for(Application::class)
            ->with(['organisation', 'fundingProgramme', 'currentSubmission'])
            ->selectRaw(
                '*,
                (SELECT name FROM organisations WHERE uuid = organisation_uuid) as organisation_name,
                (SELECT uuid FROM organisations WHERE uuid = organisation_uuid) as organisation_uuid,
                (SELECT name FROM funding_programmes WHERE uuid = funding_programme_uuid) as funding_programme_name'
            )
            ->allowedFilters([
                AllowedFilter::exact('funding_programme_uuid'),
                AllowedFilter::exact('organisation_uuid'),
                AllowedFilter::scope('current_stage'),
                AllowedFilter::scope('project_pitch_uuid'),
                AllowedFilter::scope('current_submission_status'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if (! empty($request->query('search'))) {
            $ids = Application::search(trim($request->query('search')))->get()->pluck('id')->toArray();
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new ApplicationsCollection($collection);
    }
}
