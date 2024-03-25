<?php

namespace App\Models\V2\Sites;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePolygon extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_label',
        'site_id',
        'site_name',
        'poly_label',
        'poly_id',
        'plant_date',
        'country',
        'org_name',
        'practice',
        'target_sys',
        'dist',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
