<?php

namespace App\Http\Controllers\V2\RestorationPartners;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Demographics\DemographicResource;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\EntityModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetRestorationPartnersForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     * @throws \JsonException
     */
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('update', $entity);

        $restorationPartners = $entity->restorationPartners()->visible()->get();

        // Currently we only support project-report
        $expectedCollections = match ($entity->shortName) {
            'project-report' => array_keys(DemographicCollections::RESTORATION_PARTNERS_PROJECT_COLLECTIONS),
            default => throw new NotFoundHttpException(),
        };
        $collections = $restorationPartners->pluck('collection');
        foreach ($expectedCollections as $collection) {
            if (! $collections->contains($collection)) {
                $restorationPartner = new Demographic();
                // Allows the resource to return an API response with no demographics, but still containing
                // the collection and readable collection name.
                $restorationPartner['type'] = Demographic::RESTORATION_PARTNER_TYPE;
                $restorationPartner['demographical_type'] = get_class($entity);
                $restorationPartner['collection'] = $collection;
                $restorationPartners->push($restorationPartner);
            }
        }

        return DemographicResource::collection($restorationPartners);
    }
}
