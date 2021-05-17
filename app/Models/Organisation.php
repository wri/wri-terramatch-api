<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = "App\\Models\\OrganisationVersion";

    public $guarded = [];

    function users()
    {
        return $this->hasMany("App\\Models\\User");
    }
}
