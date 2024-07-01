<?php

namespace App\Http\Controllers\V2\Disturbances;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Disturbances\DisturbanceCollection;
use App\Models\V2\Disturbance;
use App\Models\V2\EntityModel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class GetDisturbancesForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function __invoke(Request $request, EntityModel $entity): DisturbanceCollection
    {
        $this->authorize('read', $entity);

        $query = Disturbance::query()
            ->where('disturbanceable_type', get_class($entity))
            ->where('disturbanceable_id', $entity->id);

        return new DisturbanceCollection($query->paginate());
    }
}
