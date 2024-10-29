<?php

namespace App\Http\Controllers\V2\RestorationPartners;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\RestorationPartners\RestorationPartnerResource;
use App\Models\V2\EntityModel;
use App\Models\V2\RestorationPartners\RestorationPartner;
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

        $restorationPartners = RestorationPartner::where([
            'partnerable_type' => get_class($entity),
            'partnerable_id' => $entity->id,
        ])->visible()->get();

        // Currently we only support project-report
        $expectedCollections = match ($entity->shortName) {
            'project-report' => array_keys(RestorationPartner::PROJECT_COLLECTIONS),
            default => throw new NotFoundHttpException(),
        };
        $collections = $restorationPartners->pluck('collection');
        foreach ($expectedCollections as $collection) {
            if (! $collections->contains($collection)) {
                $restorationPartner = new RestorationPartner();
                // Allows the resource to return an API response with no demographics, but still containing
                // the collection and readable collection name.
                $restorationPartner['collection'] = $collection;
                $restorationPartners->push($restorationPartner);
            }
        }

        return RestorationPartnerResource::collection($restorationPartners);
    }
}
