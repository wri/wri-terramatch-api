<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteRestorationMethod extends Model
{
    public $fillable = [
        'name',
        'key',
    ];

    public function sites()
    {
        $this->belongsToMany(Site::class);
    }
}
