<?php

namespace App\Models;

use App\Models\Traits\HasDocumentFiles;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\BaselineMonitoring\HasProjectBaselineMonitoring;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programme extends Model
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;
    use HasFactory;
    use HasDocumentFiles;
    use HasProjectBaselineMonitoring;
    use SoftDeletes;

    public $fillable = [
        'name',
        'framework_id',
        'organisation_id',
        'boundary_geojson',
        'country',
        'continent',
        'end_date',
        'thumbnail',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function framework()
    {
        return $this->belongsTo(Framework::class);
    }

    public function aim()
    {
        return $this->hasOne(Aim::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function controlSites()
    {
        return $this->hasMany(Site::class)->isControlSite();
    }

    public function programmeTreeSpecies()
    {
        return $this->hasMany(ProgrammeTreeSpecies::class);
    }

    public function socioeconomicBenefits()
    {
        return $this->hasOne(SocioeconomicBenefit::class, 'programme_id');
    }

    public function editHistories()
    {
        return $this->morphMany(EditHistory::class, 'projectable');
    }

    public function inviteCode()
    {
        return $this->hasOne(FrameworkInviteCode::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function csvImports()
    {
        return $this->hasMany(CsvImport::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function media()
    {
        return $this->hasMany(MediaUpload::class);
    }

    public function programmeInvites()
    {
        return $this->hasMany(ProgrammeInvite::class);
    }

    public function dueSubmissions()
    {
        return $this->morphMany(DueSubmission::class, 'due_submissionable');
    }

    public function satelliteMonitors()
    {
        return $this->morphMany(SatelliteMonitor::class, 'satellite_monitorable');
    }

    public function getSubmittedTreeCountAttribute()
    {
        $siteIds = $this->sites->pluck('id');
        $submissionSpecies = SiteTreeSpecies::whereIn('site_id', $siteIds)
            ->whereNotNull('site_submission_id')
            ->get();

        return $submissionSpecies->sum('amount');
    }

    public function setThumbnailAttribute($thumbnail): void
    {
        $this->setAttributeByUpload('thumbnail', $thumbnail);
    }

    public function getNextDueSubmissionAttribute()
    {
        $nextSubmission = DueSubmission::forProgramme()
            ->unsubmitted()
            ->where('due_submissionable_id', $this->id)
            ->orderBy('due_at')
            ->first();

        return ! is_null($nextSubmission) ? $nextSubmission : null;
    }

    public function getTotalPaidWorkdaysAttribute(): int
    {
        $total = $this->submissions()->sum('workdays_paid');

        foreach ($this->sites as $site) {
            $total += $site->total_paid_workdays;
        }

        return $total;
    }

    public function getTotalVolunteerWorkdaysAttribute(): int
    {
        $total = $this->submissions()->sum('workdays_volunteer');

        foreach ($this->sites as $site) {
            $total += $site->total_volunteer_workdays;
        }

        return $total;
    }

    public function getTotalWorkdaysAttribute(): int
    {
        return $this->total_volunteer_workdays + $this->total_paid_workdays;
    }
}
