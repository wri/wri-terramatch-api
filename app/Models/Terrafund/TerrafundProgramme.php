<?php

namespace App\Models\Terrafund;

use App\Models\EditHistory;
use App\Models\Organisation;
use App\Models\SatelliteMonitor;
use App\Models\User;
use App\Models\V2\BaselineMonitoring\HasProjectBaselineMonitoring;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundProgramme extends Model
{
    use HasFactory;
    use HasProjectBaselineMonitoring;
    use SoftDeletes;

    public $fillable = [
        'framework_id',
        'project_country',
        'home_country',
        'boundary_geojson',
        'name',
        'description',
        'planting_start_date',
        'planting_end_date',
        'budget',
        'status',
        'history',
        'objectives',
        'environmental_goals',
        'socioeconomic_goals',
        'sdgs_impacted',
        'long_term_growth',
        'community_incentives',
        'total_hectares_restored',
        'organisation_id',
        'trees_planted',
        'jobs_created',
        'skip_submission_cycle',
    ];

    protected $with = ['terrafundTreeSpecies', 'terrafundFiles'];

    public function terrafundTreeSpecies()
    {
        return $this->morphMany(TerrafundTreeSpecies::class, 'treeable');
    }

    public function terrafundCsvImports()
    {
        return $this->morphMany(TerrafundCsvImport::class, 'importable');
    }

    public function terrafundDueSubmissions()
    {
        return $this->morphMany(TerrafundDueSubmission::class, 'terrafund_due_submissionable');
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function editHistories()
    {
        return $this->morphMany(EditHistory::class, 'projectable');
    }

    public function terrafundSites()
    {
        return $this->hasMany(TerrafundSite::class, 'terrafund_programme_id');
    }

    public function terrafundProgrammeSubmissions()
    {
        return $this->hasMany(TerrafundProgrammeSubmission::class, 'terrafund_programme_id');
    }

    public function terrafundNurseries()
    {
        return $this->hasMany(TerrafundNursery::class, 'terrafund_programme_id');
    }

    public function terrafundProgrammeInvites()
    {
        return $this->hasMany(TerrafundProgrammeInvite::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function satelliteMonitors()
    {
        return $this->morphMany(SatelliteMonitor::class, 'satellite_monitorable');
    }

    public function getNextDueSubmissionAttribute()
    {
        $nextSubmission = TerrafundDueSubmission::unsubmitted()
            ->where('terrafund_programme_id', $this->id)
            ->orderBy('due_at')
            ->first();

        return ! is_null($nextSubmission) ? $nextSubmission : null;
    }

    public function getTreesPlantedCountAttribute()
    {
        $siteIds = $this->terrafundSites()->pluck('id');
        $siteSubmissionIds = TerrafundSiteSubmission::whereIn('terrafund_site_id', $siteIds)->pluck('id');
        $submissionSpecies = TerrafundTreeSpecies::where('treeable_type', '=', TerrafundSiteSubmission::class)
            ->whereIn('treeable_id', $siteSubmissionIds)
            ->get();

        return $submissionSpecies->sum('amount');
    }

    public function getJobsCreatedCountAttribute()
    {
        $count = 0;

        $this->terrafundProgrammeSubmissions()->chunkById(100, function ($programmeSubmissions) use (&$count) {
            foreach ($programmeSubmissions as $programmeSubmission) {
                $count += $programmeSubmission->ft_total;
                $count += $programmeSubmission->pt_total;
                $count += $programmeSubmission->seasonal_total;
            }
        });

        return $count;
    }
}
