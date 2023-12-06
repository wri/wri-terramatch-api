<?php

namespace App\Models;

use App\Models\Traits\HasDocumentFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteSubmission extends Model
{
    use HasDocumentFiles;
    use SoftDeletes;
    use HasFactory;

    public const STATUS_AWAITING_APPROVAL = 'Awaiting Approval';
    public const STATUS_APPROVED = 'Approved';

    protected $with = ['site', 'siteTreeSpecies', 'disturbances', 'socioeconomicBenefits', 'mediaUploads'];

    public $fillable = [
        'site_id',
        'due_submission_id',
        'approved_at',
        'approved_by',
        'workdays_paid',
        'workdays_volunteer',
        'disturbance_information',
        'technical_narrative',
        'public_narrative',
        'site_submission_title',
        'created_by',
        'direct_seeding_kg',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function dueSubmission()
    {
        return $this->belongsTo(DueSubmission::class);
    }

    public function siteTreeSpecies()
    {
        return $this->hasMany(SiteTreeSpecies::class);
    }

    public function directSeedings()
    {
        return $this->hasMany(DirectSeeding::class);
    }

    public function disturbances()
    {
        return $this->hasMany(SiteSubmissionDisturbance::class);
    }

    public function socioeconomicBenefits()
    {
        return $this->hasOne(SocioeconomicBenefit::class);
    }

    public function mediaUploads()
    {
        return $this->hasMany(SubmissionMediaUpload::class);
    }

    public function approvedBy()
    {
        return $this->hasOne(User::class, 'approved_by');
    }

    public function getTotalWorkdaysAttribute(): int
    {
        return $this->workdays_paid + $this->workdays_volunteer;
    }
}
