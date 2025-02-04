<?php

namespace App\Models\Interfaces;

use App\Models\V2\EntityModel;

interface HandlesLinkedFieldSync
{
    public static function syncRelation(EntityModel $entity, string $property, string $inputType, $data, bool $hidden): void;
}
