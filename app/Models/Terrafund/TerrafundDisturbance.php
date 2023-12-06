<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundDisturbance extends Model
{
    use HasFactory;

    public $fillable = [
        'type',
        'description',
        'disturbanceable_type',
        'disturbanceable_id',
    ];

    public function disturbanceable()
    {
        return $this->morphTo();
    }
}
