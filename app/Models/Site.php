<?php

namespace App\Models;

use App\Models\Traits\HasDocumentFiles;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\BaselineMonitoring\HasSiteBaselineMonitoring;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SetAttributeByUploadTrait;
    use HasFactory;
    use HasDocumentFiles;
    use HasSiteBaselineMonitoring;
    use SoftDeletes;

    public $fillable = [
        'programme_id',
        'control_site',
        'name',
        'description',
        'establishment_date',
        'technical_narrative',
        'public_narrative',
        'boundary_geojson',
        'end_date',
        'history',
        'aim_survival_rate',
        'aim_year_five_crown_cover',
        'aim_direct_seeding_survival_rate',
        'aim_natural_regeneration_trees_per_hectare',
        'aim_soil_condition',
        'aim_number_of_mature_trees',
        'planting_pattern',
        'stratification_for_heterogeneity',
        'aim_natural_regeneration_hectares',
    ];

    protected $with = ['programme', 'invasives'];

    public $casts = [
        'control_site' => 'boolean',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Programme::class);
    }

    public function submissions()
    {
        return $this->hasMany(SiteSubmission::class);
    }

    public function siteRestorationMethods()
    {
        return $this->belongsToMany(SiteRestorationMethod::class, 'site_restoration_method_site');
    }

    public function landTenures()
    {
        return $this->belongsToMany(LandTenure::class);
    }

    public function siteTreeSpecies()
    {
        return $this->hasMany(SiteTreeSpecies::class)->whereNull('site_submission_id');
    }

    public function media()
    {
        return $this->hasMany(MediaUpload::class);
    }

    public function csvImports()
    {
        return $this->hasMany(SiteCsvImport::class);
    }

    public function socioeconomicBenefits()
    {
        return $this->hasOne(SocioeconomicBenefit::class, 'site_id');
    }

    public function dueSubmissions()
    {
        return $this->morphMany(DueSubmission::class, 'due_submissionable');
    }

    public function satelliteMonitors()
    {
        return $this->morphMany(SatelliteMonitor::class, 'satellite_monitorable');
    }

    public function invasives()
    {
        return $this->hasMany(Invasive::class, 'site_id', 'id');
    }

    public function scopeIsControlSite($query)
    {
        return $query->where('control_site', 1);
    }

    public function scopeExcludeControlSite($query)
    {
        return $query->where('control_site', 0);
    }

    public function setStratificationForHeterogeneityAttribute($upload): void
    {
        $this->setAttributeByUpload('stratification_for_heterogeneity', $upload);
    }

    public function getNameWithIdAttribute()
    {
        return '#' . $this->id . ' - ' . $this->name;
    }

    public function getNextDueSubmissionAttribute()
    {
        $nextSubmission = DueSubmission::forSite()
            ->unsubmitted()
            ->where('due_submissionable_id', $this->id)
            ->orderBy('due_at')
            ->first();

        return ! is_null($nextSubmission) ? $nextSubmission : null;
    }

    public function getTotalPaidWorkdaysAttribute(): int
    {
        return $this->submissions()->sum('workdays_paid');
    }

    public function getTotalVolunteerWorkdaysAttribute(): int
    {
        return $this->submissions()->sum('workdays_volunteer');
    }

    public function getTotalWorkdaysAttribute(): int
    {
        return $this->total_paid_workdays + $this->total_volunteer_workdays;
    }

    public function delayedJobs()
    {
        return $this->morphMany(DelayedJob::class, 'entity');
    }
}
