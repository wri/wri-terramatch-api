<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormSubmissionsCollection;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexFormSubmissionController extends Controller
{
    public function __invoke(Form $form, Request $request): FormSubmissionsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $query = FormSubmission::where('form_id', $form->uuid);
        $sortableColumns = [
            'id', '-id', 'name', '-name',
            'form_id', '-form_id', 'status', '-status',
            'user_id', '-user_id',
            'created_at', '-created_at', 'updated_at', '-updated_at',
            'deleted_at', '-deleted_at',
            'funding_programme_id', '-funding_programme_id',
            'stage_id', '-stage_id',
        ];

        $query = QueryBuilder::for($query)
            ->selectRaw('
                *,
                (SELECT stage_id FROM forms WHERE id = form_id) as stage_id,
                (SELECT funding_programme_id FROM stages WHERE id = stage_id) as funding_programme_id
            ')
            ->allowedFilters([
                AllowedFilter::trashed(),
                'form_id', 'status', 'user_id', 'name',
                AllowedFilter::exact('stage_id'),
                AllowedFilter::scope('funding_programme_id', 'fundingProgrammeUuid'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = FormSubmission::search(trim($request->get('search')) ?? '')
                    ->pluck('id')
                    ->toArray();

            if (empty($ids)) {
                return new FormSubmissionsCollection([]);
            }
            $query->whereIn('id', $ids);
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new FormSubmissionsCollection($collection);
    }
}
