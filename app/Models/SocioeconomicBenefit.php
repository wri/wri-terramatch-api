<?php

namespace App\Models;

use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class SocioeconomicBenefit extends Model
{
    use SetAttributeByUploadTrait;

    protected $touches = ['siteSubmission', 'submission', 'programme', 'site'];

    protected $fillable = [
        'name',
        'site_id',
        'site_submission_id',
        'programme_id',
        'programme_submission_id',
        'upload',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
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
