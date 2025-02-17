<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class PolygonUpdates extends Model
{

    protected $fillable = [
        'site_polygon_uuid',
        'version_name',
        'change',
        'updated_by_id',
        'comment',
        'type',
    ];

    public function scopeLastWeek($query)
    {
        return $query->where('created_at', '>=', now()->subWeek());
    }

    public function scopeIsUpdate($query)
    {
        return $query->where('type', 'update');
    }

    public function scopeIsStatus($query)
    {
        return $query->where('type', 'status');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
