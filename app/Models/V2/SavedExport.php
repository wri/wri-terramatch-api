<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SavedExport extends Model implements HasMedia
{
    use HasUuid;
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = ['uuid', 'name', 'funding_programme_id'];
}
