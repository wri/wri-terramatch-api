<?php

namespace App\Models\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface HandlesLinkedFieldSync
{
    public static function syncRelation(Model $entity, string $property, string $inputType, $data, bool $hidden): void;
}
