<?php

namespace App\Models\V2\Trackings;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string uuid
 * @property string trackable_type
 * @property int trackable_id
 * @property string domain
 * @property string type
 * @property string collection
 * @property string description
 * @property bool hidden
 */
class Tracking extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public const DEMOGRAPHICS_COUNT_CUTOFF = '2024-07-05';

    public const WORKDAY_TYPE = 'workdays';
    public const RESTORATION_PARTNER_TYPE = 'restoration-partners';
    public const JOBS_TYPE = 'jobs';
    public const VOLUNTEERS_TYPE = 'volunteers';
    public const EMPLOYEES_TYPE = 'employees';
    public const ALL_BENEFICIARIES_TYPE = 'all-beneficiaries';
    public const TRAINING_BENEFICIARIES_TYPE = 'training-beneficiaries';
    public const INDIRECT_BENEFICIARIES_TYPE = 'indirect-beneficiaries';
    public const ASSOCIATES_TYPE = 'associates';

    public const VALID_TYPES = [
        self::WORKDAY_TYPE,
        self::RESTORATION_PARTNER_TYPE,
        self::JOBS_TYPE,
        self::VOLUNTEERS_TYPE,
        self::EMPLOYEES_TYPE,
        self::ALL_BENEFICIARIES_TYPE,
        self::TRAINING_BENEFICIARIES_TYPE,
        self::INDIRECT_BENEFICIARIES_TYPE,
    ];

    // In TM-1681 we moved several "name" values to "subtype". This check helps make sure that both in-flight
    // work at the time of release, and updates from update requests afterward honor that change.
    protected const SUBTYPE_SWAP_TYPES = [TrackingEntry::GENDER, TrackingEntry::AGE, TrackingEntry::CASTE];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    protected $fillable = [
        'uuid',
        'trackable_type',
        'trackable_id',
        'type',
        'collection',
        'description',
        'hidden',
        'domain',
    ];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TrackingEntry::class);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    public function scopeCollections(Builder $query, array $collections): Builder
    {
        return $query->whereIn('collection', $collections);
    }

    public function scopeVisible($query): Builder
    {
        return $query->where('hidden', false);
    }
}
