<?php

namespace App\Models\Terrafund;

use App\Models\V2\BaselineMonitoring\HasSiteBaselineMonitoring;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundSite extends Model
{
    use HasFactory;
    use HasSiteBaselineMonitoring;
    use SoftDeletes;

    public $fillable = [
        'name',
        'start_date',
        'end_date',
        'restoration_methods',
        'boundary_geojson',
        'hectares_to_restore',
        'landscape_community_contribution',
        'disturbances',
        'land_tenures',
        'skip_submission_cycle',
    ];

    protected $with = ['terrafundFiles'];

    public $casts = [
        'restoration_methods' => 'array',
        'land_tenures' => 'array',
    ];

    public function terrafundDueSubmissions()
    {
        return $this->morphMany(TerrafundDueSubmission::class, 'terrafund_due_Submissionable');
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function terrafundSiteSubmissions()
    {
        return $this->hasMany(TerrafundSiteSubmission::class);
    }

    public function terrafundTreeSpecies()
    {
        return $this->morphMany(TerrafundTreeSpecies::class, 'treeable');
    }
}
