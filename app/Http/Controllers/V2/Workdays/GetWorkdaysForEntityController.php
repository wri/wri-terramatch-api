<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Workdays\WorkdaysCollection;
use App\Models\V2\EntityModel;
use App\Models\V2\Workdays\Workday;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetWorkdaysForEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity)
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $this->authorize('update', $entity);

        $qry = QueryBuilder::for(Workday::class)
            ->where('workdayable_type', get_class($entity))
            ->where('workdayable_id', $entity->id)
            ->allowedFilters([
                AllowedFilter::exact('collection'),
            ]);

        $totalAmount = $qry->sum('amount');

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return (new WorkdaysCollection($collection))->params(['count_total' => $totalAmount ]);
    }
}
