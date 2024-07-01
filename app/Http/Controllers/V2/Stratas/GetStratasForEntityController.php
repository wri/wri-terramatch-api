<?php

namespace App\Http\Controllers\V2\Stratas;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Stratas\StratasCollection;
use App\Models\V2\EntityModel;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Stratas\Strata;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetStratasForEntityController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function __invoke(Request $request, EntityModel $entity): StratasCollection
    {
        $this->authorize('read', $entity);

        $query = Strata::query()
            ->where('stratasable_type', get_class($entity))
            ->where('stratasable_id', $entity->id);

        return new StratasCollection($query->paginate());
    }
}
