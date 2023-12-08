<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteCsvImport extends Model
{
    public $fillable = [
        'site_id',
        'site_submission_id',
        'has_failed',
        'total_rows',
    ];

    public $casts = [
        'has_failed' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function siteTreeSpecies()
    {
        return $this->hasMany(SiteTreeSpecies::class);
    }
}
