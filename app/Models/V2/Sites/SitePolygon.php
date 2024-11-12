<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\PointGeometry;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\Services\PolygonService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
      'primary_uuid',
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
      'source',
      'is_active',
      'version_name',
    ];

    public function polygonGeometry(): BelongsTo
    {
        return $this->belongsTo(PolygonGeometry::class, 'poly_id', 'uuid');
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(PointGeometry::class, 'point_id', 'uuid');
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

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function changeStatusOnEdit()
    {
        if ($this->status === 'approved') {
            $this->status = 'submitted';
            $this->save();
        }
    }

    public function createCopy(User $user, ?string $poly_id = null, ?bool $submit_polygon_loaded = false, ?array $properties = [])
    {
        $geometry = $this->polygonGeometry()->first();
        SitePolygon::where('primary_uuid', $this->primary_uuid)->update(['is_active' => false]);

        $newSitePolygon = $this->replicate();
        $currentSite = Site::where('uuid', $newSitePolygon->site_id)->exists();
        if (! $currentSite) {
            if (isset($properties['site_id'])) {
                $siteExists = Site::where('uuid', $properties['site_id'])->exists();
                if (! $siteExists) {
                    Log::info('Provided Site UUID not found = ' . $properties['site_id'] . ' for SitePolygon UUID = ' . $newSitePolygon->uuid);

                    return null;
                }
                Log::info($this->primary_uuid.'Changing sites from ' . $newSitePolygon->site_id . ' to ' . $properties['site_id']);
                $newSitePolygon->site_id = $properties['site_id'];
            } else {
                Log::info('Site UUID not found = ' . $newSitePolygon->site_id . ' for SitePolygon UUID = ' . $newSitePolygon->uuid);

                return null;
            }
        }
        App::make(PolygonService::class)->moveAllCriteriaSite($geometry->uuid);
        if (! $poly_id) {
            $copyGeometry = PolygonGeometry::create([
                'geom' => $geometry->geom,
                'created_by' => $user->id,
            ]);
        }
        $newSitePolygon->primary_uuid = $this->primary_uuid;
        $newSitePolygon->plantstart = $properties['plantstart'] ?? $this->plantstart;
        $newSitePolygon->plantend = $properties['plantend'] ?? $this->plantend;
        $newSitePolygon->practice = $properties['practice'] ?? $this->practice;
        $newSitePolygon->target_sys = $properties['target_sys'] ?? $this->target_sys;
        $newSitePolygon->distr = $properties['distr'] ?? $this->distr;
        $newSitePolygon->num_trees = $properties['num_trees'] ?? $this->num_trees;
        $newSitePolygon->poly_id = $poly_id ?? $copyGeometry->uuid;
        $newSitePolygon->poly_name = $submit_polygon_loaded ? $this->poly_name.' (new)' : $properties['poly_name'] ?? $this->poly_name;
        $newSitePolygon->version_name = ($properties['poly_name'] ?? $this->poly_name).'_'.now()->format('j_F_Y_H_i_s').'_'.$user->full_name;
        $newSitePolygon->is_active = true;
        $newSitePolygon->uuid = (string) Str::uuid();
        $newSitePolygon->created_by = $user->id;
        $newSitePolygon->save();

        return $newSitePolygon;
    }

    protected static function booted()
    {
        static::created(function ($instance) {
            if (! is_null($instance->primary_uuid)) {
                return;
            }
            $instance->primary_uuid = $instance->uuid;
            $instance->save();
        });
    }
}
