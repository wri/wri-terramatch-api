<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundCsvImport extends Model
{
    use HasFactory;

    public $fillable = [
        'importable_type',
        'importable_id',
        'has_failed',
        'total_rows',
    ];

    public function importable()
    {
        return $this->morphTo();
    }

    public function terrafundTreeSpecies()
    {
        return $this->hasMany(TerrafundTreeSpecies::class);
    }
}
