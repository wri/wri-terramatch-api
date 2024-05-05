<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Workdays\WorkdayResource;
use App\Models\V2\EntityModel;
use App\Models\V2\Workdays\Workday;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetWorkdaysForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     * @throws \JsonException
     */
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('update', $entity);

        $workdays = Workday::where([
            'workdayable_type' => get_class($entity),
            'workdayable_id' => $entity->id,
        ])->get();

        $collections = match ($entity->shortName) {
            'site-report' => array_keys(Workday::$siteCollections),
            'project-report' => array_keys(Workday::$projectCollections),
            default => throw new NotFoundHttpException(),
        };
        foreach ($collections as $collection) {
            if (!$workdays->keys()->contains($collection)) {
                $workday = new Workday();
                // Allows the resource to return an API response with no demographics, but still containing
                // the collection and readable collection name.
                $workday['collection'] = $collection;
                $workdays->push($workday);
            }
        }

        return WorkdayResource::collection($workdays);
    }
}
