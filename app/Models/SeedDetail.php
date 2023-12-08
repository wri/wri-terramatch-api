<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedDetail extends Model
{
    public $fillable = [
        'name',
        'weight_of_sample',
        'site_id',
        'seeds_in_sample',
    ];

    public $casts = [
        'weight_of_sample' => 'decimal:4',
        'seeds_in_sample' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getSeedsPerKGAttribute(): float
    {
        if (data_get($this, 'weight_of_sample', 0) <= 0 || data_get($this, 'seeds_in_sample', 0) <= 0) {
            return 0;
        }

        $perKG = $this->seeds_in_sample * (1 / $this->weight_of_sample);

        return round($perKG, 4);
    }
}
