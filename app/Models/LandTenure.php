<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandTenure extends Model
{
    public $fillable = [
        'name',
        'key',
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class);
    }
}
