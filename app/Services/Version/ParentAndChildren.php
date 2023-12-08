<?php

namespace App\Services\Version;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ParentAndChildren
{
    public $parent = null;

    public $children = null;

    public function __construct(Model $parent, Collection $children)
    {
        $this->parent = $parent;
        $this->children = $children;
    }
}
