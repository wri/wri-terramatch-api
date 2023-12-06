<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTreeSpecies extends Model
{
    protected $fillable = [
        'site_id',
        'site_csv_import_id',
        'site_submission_id',
        'name',
        'amount',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
