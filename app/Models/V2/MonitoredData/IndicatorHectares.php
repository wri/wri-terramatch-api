<?php

namespace App\Models\V2\MonitoredData;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorHectares extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'indicator_output_hectares';

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
