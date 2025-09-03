<?php

namespace App\Models\V2;

use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DisturbanceReport extends Model implements AuditableContract
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasEntityResources;
    use Auditable;

    protected $table = 'disturbance_reports';

    protected $fillable = [
        'uuid',
        'disturbanceable_type',
        'disturbanceable_id',
        'disturbance_date',
        'collection',
        'type',
        'subtype',
        'intensity',
        'extent',
        'people_affected',
        'monetary_damage',
        'description',
        'action_description',
        'property_affected',
        'old_id',
        'old_model',
        'hidden',
    ];

    protected $casts = [
        'disturbance_date' => 'date',
        'people_affected'  => 'integer',
        'monetary_damage'  => 'decimal:2',
        'hidden'           => 'boolean',
    ];

    public $shortName = 'disturbance-report';

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Polymorphic relation (any model can have disturbance reports).
     */
    public function disturbanceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getAuditableNameAttribute(): string
    {
        return "Disturbance Report #{$this->id}";
    }
}
