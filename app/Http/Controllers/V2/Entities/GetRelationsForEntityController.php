<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\Disturbance;
use App\Models\V2\EntityModel;
use App\Models\V2\EntityRelationModel;
use App\Models\V2\Invasive;
use App\Models\V2\Stratas\Strata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetRelationsForEntityController extends Controller
{
    public const RELATIONS = [
        'disturbances' => Disturbance::class,
        'stratas' => Strata::class,
        'invasives' => Invasive::class,
    ];

    public function __invoke(Request $request, string $relationType, EntityModel $entity): JsonResource
    {
        $this->authorize('read', $entity);

        /** @var EntityRelationModel $type */
        $type = self::RELATIONS[$relationType];

        return $type::createResourceCollection($entity);
    }
}
