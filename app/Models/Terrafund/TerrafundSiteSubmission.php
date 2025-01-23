<?php

namespace App\Models\Terrafund;

use App\Models\Interfaces\TerrafundSubmissionInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundSiteSubmission extends Model implements TerrafundSubmissionInterface
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'terrafund_site_id',
        'shared_drive_link',
    ];

    protected $with = ['terrafundTreeSpecies', 'terrafundNoneTreeSpecies', 'terrafundFiles', 'disturbances'];

    public function terrafundSite()
    {
        return $this->belongsTo(TerrafundSite::class);
    }

    public function terrafundDueSubmissionable()
    {
        return  $this->terrafundSite()->first();
    }

    public function terrafundDueSubmission()
    {
        return $this->belongsTo(TerrafundDueSubmission::class);
    }

    public function terrafundTreeSpecies()
    {
        return $this->morphMany(TerrafundTreeSpecies::class, 'treeable');
    }

    public function terrafundNoneTreeSpecies()
    {
        return $this->morphMany(TerrafundNoneTreeSpecies::class, 'speciesable');
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function disturbances()
    {
        return $this->morphMany(TerrafundDisturbance::class, 'disturbanceable');
    }

    public function scopeSubmissionsBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate->startOfDay(),$endDate->endOfDay()]);
    }
}
