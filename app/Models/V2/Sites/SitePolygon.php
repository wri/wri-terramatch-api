<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

/**
 * @method static forPolygonGeometry($value):  Builder
 */
class SitePolygon extends Model implements AuditableModel
{
    use HasUuid;
    use SoftDeletes;
    use BelongsToThroughTrait;
    use HasFactory;

    protected $table = 'site_polygon';

    protected $fillable = [
      'poly_id',
      'poly_name',
      'site_id',
      'point_id',
      'plantstart',
      'plantend',
      'practice',
      'target_sys',
      'distr',
      'num_trees',
      'calc_area',
      'status',
      'created_by',
    ];

    public function polygonGeometry(): BelongsTo
    {
        return $this->belongsTo(PolygonGeometry::class, 'poly_id', 'uuid');
    }

    public function scopeForPolygonGeometry($query, $uuid): Builder
    {
        return $query->where('poly_id', $uuid);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Project::class,
            Site::class,
            foreignKeyLookup: [Project::class => 'project_id', Site::class => 'site_id'],
            localKeyLookup: [Site::class => 'uuid']
        );
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'uuid');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }

    public function getAuditableNameAttribute(): string
    {
        return $this->poly_name ?? '';
    }
}
