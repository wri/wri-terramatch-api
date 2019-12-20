<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasVersions;

    protected $versionClass = OrganisationVersion::class;

    public $guarded = [];

    function users()
    {
        return $this->hasMany(User::class);
    }
}
