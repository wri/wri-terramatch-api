<?php

namespace App\Http\Controllers\V2\Invasives;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Invasives\InvasiveCollection;
use App\Models\V2\EntityModel;
use App\Models\V2\Invasive;
use Illuminate\Http\Request;

class GetInvasivesForEntityController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity)
    {
        $this->authorize('read', $entity);

        $query = Invasive::query()
            ->where('invasiveable_type', get_class($entity))
            ->where('invasiveable_id', $entity->id);

        return new InvasiveCollection($query->paginate());
    }
}
