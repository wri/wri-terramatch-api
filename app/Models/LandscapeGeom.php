<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandscapeGeom extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'landscape_geom';

    protected $fillable = ['landscape', 'geometry'];

    public function scopeForLandscape($query, string $landscape)
    {
        return $query->where('landscape', $landscape);
    }
}
