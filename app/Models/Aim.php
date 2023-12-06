<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aim extends Model
{
    use HasFactory;

    public $fillable = [
        'programme_id',
        'year_five_trees',
        'restoration_hectares',
        'survival_rate',
        'year_five_crown_cover',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
}
