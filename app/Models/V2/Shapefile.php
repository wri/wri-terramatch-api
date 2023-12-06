<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Shapefile extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    public function shapefileable(): MorphTo
    {
        return $this->morphTo();
    }
}
