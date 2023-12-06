<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundTreeSpecies extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'amount',
        'treeable_type',
        'treeable_id',
        'terrafund_csv_import_id',
    ];

    public function treeable()
    {
        return $this->morphTo();
    }
}
