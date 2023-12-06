<?php

namespace App\Models\Traits;

trait NamedEntityTrait
{
    public function getEntityName(): string
    {
        return explode_pop('\\', get_class($this));
    }
}
