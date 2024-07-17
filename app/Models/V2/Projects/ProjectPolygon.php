<?php

namespace App\Models\V2\Projects;

use App\Models\Traits\HasUuid;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static forPolygonGeometry($value): Builder
 */
class ProjectPolygon extends Model implements AuditableModel
{
    use HasUuid;
    use SoftDeletes;
    use HasFactory;

    protected $table = 'project_polygon';

    protected $fillable = [
        'poly_uuid',
        'entity_type',
        'entity_id',
        'last_modified_by',
        'created_by',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function polygonGeometry(): BelongsTo
    {
        return $this->belongsTo(PolygonGeometry::class, 'poly_uuid', 'uuid');
    }

    public function scopeForPolygonGeometry($query, $uuid): Builder
    {
        return $query->where('poly_uuid', $uuid);
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function lastModifiedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'last_modified_by');
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
        return $this->entity_type . ' Polygon';
    }
}
