<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static forIso($countryIso): Builder
 */
class WorldCountryGeneralized extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'world_countries_generalized';

    protected $fillable = [
        'countryaff', 'country', 'iso', 'country_aff', 'aff_iso', 'geometry', 'OGR_FID',
    ];

    public function scopeForIso($query, string $iso): Builder
    {
        return $query->where('iso', $iso);
    }
}
