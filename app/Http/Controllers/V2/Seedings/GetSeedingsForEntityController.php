<?php

namespace App\Http\Controllers\V2\Seedings;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seedings\SeedingsCollection;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetSeedingsForEntityController extends Controller
{
    public function __invoke(Request $request, string $entity, string $uuid)
    {
        $model = $this->getModel($entity);

        if (is_null($model)) {
            return new JsonResponse($entity . ' is not a valid entity key', 422);
        }

        $object = $model::isUuid($uuid)->first();

        $this->authorize('read', $object);

        if (is_null($object)) {
            return new JsonResponse($entity . ' record not found', 404);
        }

        $query = Seeding::query()
            ->where('seedable_type', $model)
            ->where('seedable_id', $object->id);

        return new SeedingsCollection($query->paginate());
    }

    private function getModel(string $entity)
    {
        $model = null;

        switch ($entity) {
            case 'site':
            case 'sites':
                $model = Site::class;

                break;
            case 'site-report':
            case 'site-reports':
                $model = SiteReport::class;

                break;
        }

        return $model;
    }
}
