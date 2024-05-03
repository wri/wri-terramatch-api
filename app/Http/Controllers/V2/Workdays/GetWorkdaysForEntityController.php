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
        $this->authorize('update', $entity);

        $qry = QueryBuilder::for(Workday::class)
            ->where('workdayable_type', get_class($entity))
            ->where('workdayable_id', $entity->id)
            ->allowedFilters([
                AllowedFilter::exact('collection'),
            ]);

        return new WorkdaysCollection($qry->get());
    }
}
