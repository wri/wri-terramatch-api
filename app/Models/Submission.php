<?php

namespace App\Models;

use App\Models\Traits\HasDocumentFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasDocumentFiles;
    use SoftDeletes;
    use HasFactory;

    public $fillable = [
        'programme_id',
        'due_submission_id',
        'site_id',
        'approved_at',
        'approved_by',
        'workdays_paid',
        'workdays_volunteer',
        'title',
        'technical_narrative',
        'public_narrative',
        'created_by',
    ];

    public const STATUS_AWAITING_APPROVAL = 'Awaiting Approval';
    public const STATUS_APPROVED = 'Approved';

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function dueSubmission()
    {
        return $this->belongsTo(DueSubmission::class);
    }

    public function mediaUploads()
    {
        return $this->hasMany(SubmissionMediaUpload::class);
    }

    public function csvImports()
    {
        return $this->hasMany(CsvImport::class, 'programme_submission_id');
    }

    public function programmeTreeSpecies()
    {
        return $this->hasMany(ProgrammeTreeSpecies::class, 'programme_submission_id');
    }

    public function socioeconomicBenefits()
    {
        return $this->hasOne(SocioeconomicBenefit::class, 'programme_submission_id');
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
