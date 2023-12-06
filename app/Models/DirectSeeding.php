<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectSeeding extends Model
{
    public $fillable = [
        'name',
        'weight',
        'site_submission_id',
    ];

    public function siteSubmission()
    {
        return $this->belongsTo(SiteSubmission::class);
    }
}
