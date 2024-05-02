<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class WorldCountryGeneralized extends Model
{
    protected $table = 'world_countries_generalized';

    protected $fillable = [
        'countryaff', 'country', 'iso', 'country_aff', 'aff_iso', 'geometry', 'OGR_FID',
    ];
}
