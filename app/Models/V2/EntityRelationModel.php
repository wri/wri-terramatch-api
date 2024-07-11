<?php

namespace App\Models\V2;

use Illuminate\Http\Resources\Json\JsonResource;

interface EntityRelationModel
{
    public static function createResourceCollection(EntityModel $entity): JsonResource;
}
