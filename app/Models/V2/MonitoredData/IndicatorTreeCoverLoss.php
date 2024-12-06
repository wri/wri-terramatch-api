<?php

namespace App\Models\V2\MonitoredData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\V2\Sites\SitePolygon;

class IndicatorTreeCoverLoss extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'indicator_output_tree_cover_loss';

    protected $fillable = [
        'year_of_analysis',
        'value',
        'indicator_slug',
        'site_polygon_id',  
    ];

    public function sitePolygon(): BelongsTo
    {
        return $this->belongsTo(SitePolygon::class, 'site_polygon_id', 'id');
    }
}
