<?php

namespace App\Models\V2\MonitoredData;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndicatorTreeCover extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'indicator_output_tree_cover';

    protected $fillable = [
        'year_of_analysis',
        'percent_cover',
        'project_phase',
        'plus_minus_percent',
        'indicator_slug',
        'site_polygon_id',
    ];

    public function sitePolygon(): BelongsTo
    {
        return $this->belongsTo(SitePolygon::class, 'site_polygon_id', 'id');
    }
}
