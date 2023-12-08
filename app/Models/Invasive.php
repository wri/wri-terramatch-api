<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invasive extends Model
{
    public $fillable = [
        'name',
        'type',
        'site_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
