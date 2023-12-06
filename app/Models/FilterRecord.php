<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class FilterRecord extends Model
{
    use NamedEntityTrait;

    public $guarded = [];

    public $casts = [
        'land_types' => 'boolean',
        'land_ownerships' => 'boolean',
        'land_size' => 'boolean',
        'land_continent' => 'boolean',
        'land_country' => 'boolean',
        'restoration_methods' => 'boolean',
        'restoration_goals' => 'boolean',
        'funding_sources' => 'boolean',
        'funding_amount' => 'boolean',
        'long_term_engagement' => 'boolean',
        'reporting_frequency' => 'boolean',
        'reporting_level' => 'boolean',
        'sustainable_development_goals' => 'boolean',
        'price_per_tree' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
