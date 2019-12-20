<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterRecord extends Model
{
    public $fillable = [
        'user_id',
        'organisation_id',
        'type',
        'land_types',
        'land_ownerships',
        'land_size',
        'land_continent',
        'land_country',
        'restoration_methods',
        'restoration_goals',
        'funding_sources',
        'funding_amount',
        'long_term_engagement',
        'reporting_frequency',
        'reporting_level',
        'sustainable_development_goals',
        'price_per_tree'
    ];
}
