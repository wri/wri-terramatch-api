<?php

namespace App\Http\Controllers\V2\Forms;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Forms\FormsCollection;
use App\Models\V2\Forms\Form;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexFormController extends Controller
{
    public function __invoke(Request $request): FormsCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $query = QueryBuilder::for(Form::class)
            ->allowedFilters([
                'title', 'subtitle',
                'description', 'submission_message',
                'document', 'duration', 'published',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('framework_key'),
                AllowedFilter::exact('stage_id'),
                AllowedFilter::exact('stage.funding_programme_id'),
                AllowedFilter::trashed(),
            ])
            ->allowedSorts([
                'stage_id', 'title', 'subtitle',
                'description', 'submission_message',
                'document', 'duration', 'published',
                'created_at', 'updated_at', 'deleted_at',
            ]);

        $filter = $request->query('filter');
        if ($filter) {
            $query->whereNull('framework_key');
        }

        if ($request->query('search')) {
            $ids = Form::search(trim($request->get('search')) ?? '')
                    ->pluck('id')
                    ->toArray();

            if (empty($ids)) {
                return new FormsCollection([]);
            }
            $query->whereIn('id', $ids);
        }

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new FormsCollection($collection);
    }
}
