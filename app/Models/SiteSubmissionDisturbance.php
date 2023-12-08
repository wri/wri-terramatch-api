<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSubmissionDisturbance extends Model
{
    protected $touches = ['siteSubmission'];

    public $fillable = [
        'site_submission_id',
        'disturbance_type',
        'intensity',
        'description',
        'extent',
    ];

    public function siteSubmission()
    {
        return $this->belongsTo(SiteSubmission::class);
    }
}
