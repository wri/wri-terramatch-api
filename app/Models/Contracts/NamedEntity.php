<?php

namespace App\Models\Contracts;

interface NamedEntity
{
    public function getEntityName(): string;
}
