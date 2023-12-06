<?php

namespace App\Models\V2\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;

class UsersOrganisationsSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'DESC' : 'ASC';

        $query->orderByRaw("LENGTH(`{$property}`) {$direction}");
    }
}
