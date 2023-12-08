<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundNoneTreeSpecies extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'amount',
        'speciesable_type',
        'speciesable_id',
    ];

    public function speciesable()
    {
        return $this->morphTo();
    }
}
