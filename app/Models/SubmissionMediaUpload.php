<?php

namespace App\Models;

use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class SubmissionMediaUpload extends Model
{
    use SetAttributeByUploadTrait;

    public $fillable = [
        'media_title',
        'is_public',
        'submission_id',
        'upload',
        'location_long',
        'location_lat',
        'site_submission_id',
    ];

    public $casts = [
        'is_public' => 'boolean',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function siteSubmission()
    {
        return $this->belongsTo(SiteSubmission::class);
    }

    public function setUploadAttribute($upload): void
    {
        $this->setAttributeByUpload('upload', $upload);
    }
}
