<?php

namespace App\Http\Controllers\V2\Stages;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Stages\StagesCollection;
use App\Models\V2\Stages\Stage;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexStageController extends Controller
{
    public function __invoke(Request $request): StagesCollection
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $query = QueryBuilder::for(Stage::class)
            ->allowedFilters([
                'funding_programme_id',
                AllowedFilter::trashed(),
            ])
            ->allowedSorts([
                'funding_programme_id',
                'created_at', 'updated_at', 'deleted_at',
            ]);

        $collection = $query->paginate($perPage)
            ->appends(request()->query());

        return new StagesCollection($collection);
    }
}
