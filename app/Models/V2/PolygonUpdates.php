<?php

namespace App\Models\V2;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolygonUpdates extends Model
{
    use HasFactory;
    use HasStatus;

    protected $fillable = [
        'site_polygon_uuid',
        'version_name',
        'change',
        'updated_by_id',
        'comment',
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
