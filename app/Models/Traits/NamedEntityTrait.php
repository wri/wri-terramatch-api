<?php

namespace App\Models\Traits;

trait NamedEntityTrait
{
    public function getEntityName(): string
    {
        if (property_exists($this, 'entityName')) {
            return $this->entityName;
        }
        return explode_pop("\\", get_class($this));
    }
}
