<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Pitch extends Model
{
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = "App\\Models\\PitchVersion";

    public $table = "pitches";
    public $guarded = [];

    public function organisation()
    {
        return $this->belongsTo("App\\Models\\Organisation");
    }
}
