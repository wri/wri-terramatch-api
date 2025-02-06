<?php

namespace App\Http\Controllers\V2\Demographics;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Demographics\DemographicResource;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\EntityModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetDemographicsForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     * @throws \JsonException
     */
    public function __invoke(Request $request, string $demographicType, EntityModel $entity)
    {
        $this->authorize('update', $entity);

        $property = Str::camel($demographicType);
        $demographics = $entity->$property()->visible()->get();

        $expectedCollections = match ($demographicType) {
            Demographic::RESTORATION_PARTNER_TYPE => match ($entity->shortName) {
                'project-report' => array_keys(DemographicCollections::RESTORATION_PARTNERS_PROJECT_COLLECTIONS),
                default => throw new NotFoundHttpException(),
            },
            Demographic::WORKDAY_TYPE => match ($entity->shortName) {
                'site-report' => array_keys(DemographicCollections::WORKDAYS_SITE_COLLECTIONS),
                'project-report' => array_keys(DemographicCollections::WORKDAYS_PROJECT_COLLECTIONS),
                default => throw new NotFoundHttpException(),
            },
            default => throw new NotFoundHttpException()
        };
        $collections = $demographics->pluck('collection');
        foreach ($expectedCollections as $collection) {
            if (! $collections->contains($collection)) {
                $demographic = new Demographic();
                // Allows the resource to return an API response with no demographics, but still containing
                // the collection and readable collection name.
                $demographic['type'] = $demographicType;
                $demographic['demographical_type'] = get_class($entity);
                $demographic['collection'] = $collection;
                $demographics->push($demographic);
            }
        }

        return DemographicResource::collection($demographics);
    }
}
