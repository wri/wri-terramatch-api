<?php

namespace App\Services\Version;

use Illuminate\Database\Eloquent\Model;

class ParentAndChild
{
    public $parent = null;

    public $child = null;

    public function __construct(Model $parent, ?Model $child)
    {
        $this->parent = $parent;
        $this->child = $child;
    }
}
