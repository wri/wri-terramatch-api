<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Workdays\WorkdaysCollection;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetWorkdaysForEntityController extends Controller
{
    public function __invoke(Request $request, string $entity, string $uuid)
    {
        $model = $this->getModel($entity);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);

        if (is_null($model)) {
            return new JsonResponse($entity . ' is not a valid entity key', 422);
        }

        $object = $model::isUuid($uuid)->first();

        $this->authorize('update', $object);

        if (is_null($object)) {
            return new JsonResponse($entity . ' record not found', 404);
        }

        $qry = QueryBuilder::for(Workday::class)
            ->where('workdayable_type', $model)
            ->where('workdayable_id', $object->id)
            ->allowedFilters([
                AllowedFilter::exact('collection'),
            ]);

        $totalAmount = $qry->sum('amount');

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return (new WorkdaysCollection($collection))->params(['count_total' => $totalAmount ]);
    }

    private function getModel(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'project-report':
                $model = ProjectReport::class;

                break;
            case 'site-report':
                $model = SiteReport::class;
        }

        return $model;
    }
}
