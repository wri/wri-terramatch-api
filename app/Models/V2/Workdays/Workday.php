<?php

namespace App\Models\V2\Workdays;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\EntityModel;
use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property Collection $demographics
 */
class Workday extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;

    protected $casts = [
        'published' => 'boolean',
    ];

    public $table = 'v2_workdays';

    protected $fillable = [
        'workdayable_type',
        'workdayable_id',
        'framework_key',
        'amount',
        'collection',
        'gender',
        'age',
        'ethnicity',
        'indigeneity',
        'migrated_to_demographics',
    ];

    public const COLLECTION_PROJECT_PAID_NURSERY_OPRERATIONS = 'paid-nursery-operations';
    public const COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT = 'paid-project-management';
    public const COLLECTION_PROJECT_PAID_OTHER = 'paid-other-activities';
    public const COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPRERATIONS = 'volunteer-nursery-operations';
    public const COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT = 'volunteer-project-management';
    public const COLLECTION_PROJECT_VOLUNTEER_OTHER = 'volunteer-other-activities';

    public static $projectCollections = [
        self::COLLECTION_PROJECT_PAID_NURSERY_OPRERATIONS => 'Paid Nursery Operations',
        self::COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT => 'Paid Project Management',
        self::COLLECTION_PROJECT_PAID_OTHER => 'Paid Other Activities',
        self::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPRERATIONS => 'Volunteer Nursery Operations',
        self::COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT => 'Volunteer Project Management',
        self::COLLECTION_PROJECT_VOLUNTEER_OTHER => 'Volunteer Other Activities',
    ];

    public const COLLECTION_SITE_PAID_SITE_ESTABLISHMENT = 'paid-site-establishment';
    public const COLLECTION_SITE_PAID_PLANTING = 'paid-planting';
    public const COLLECTION_SITE_PAID_SITE_MAINTENANCE = 'paid-site-maintenance';
    public const COLLECTION_SITE_PAID_SITE_MONITORING = 'paid-site-monitoring';
    public const COLLECTION_SITE_PAID_OTHER = 'paid-other-activities';
    public const COLLECTION_SITE_VOLUNTEER_SITE_ESTABLISHMENT = 'volunteer-site-establishment';
    public const COLLECTION_SITE_VOLUNTEER_PLANTING = 'volunteer-planting';
    public const COLLECTION_SITE_VOLUNTEER_SITE_MAINTENANCE = 'volunteer-site-maintenance';
    public const COLLECTION_SITE_VOLUNTEER_SITE_MONITORING = 'volunteer-site-monitoring';
    public const COLLECTION_SITE_VOLUNTEER_OTHER = 'volunteer-other-activities';

    public static $siteCollections = [
        self::COLLECTION_SITE_PAID_SITE_ESTABLISHMENT => 'Paid Site Establishment',
        self::COLLECTION_SITE_PAID_PLANTING => 'Paid Planting',
        self::COLLECTION_SITE_PAID_SITE_MAINTENANCE => 'Paid Site Maintenance',
        self::COLLECTION_SITE_PAID_SITE_MONITORING => 'Paid Site Monitoring',
        self::COLLECTION_SITE_PAID_OTHER => 'Paid Other Activities',
        self::COLLECTION_SITE_VOLUNTEER_SITE_ESTABLISHMENT => 'Volunteer Site Establishment',
        self::COLLECTION_SITE_VOLUNTEER_PLANTING => 'Volunteer Planting',
        self::COLLECTION_SITE_VOLUNTEER_SITE_MAINTENANCE => 'Volunteer Site Maintenance',
        self::COLLECTION_SITE_VOLUNTEER_SITE_MONITORING => 'Volunteer Site Monitoring',
        self::COLLECTION_SITE_VOLUNTEER_OTHER => 'Volunteer Other Activities',
    ];

    public static function syncRelation(EntityModel $entity, string $property, $data): void
    {
        // Workdays only have one instance per collection
        $workday = $entity->$property()->first();
        if ($workday != null && $workday->collection != $data['collection']) {
            throw new InvalidArgumentException(
                'Workday collection does not match entity property [' .
                'property collection: ' . $workday->collection . ', ' .
                'submitted collection: ' . $data['collection'] . ']'
            );
        }

        if ($workday == null) {
            $workday = Workday::create([
                'workdayable_type' => get_class($entity),
                'workdayable_id' => $entity->id,
                'collection' => $data['collection'],
            ]);
        }

        foreach ($data['demographics'] as $demographicData) {
            $demographic = $workday->demographics()->where([
                'type' => $demographicData['type'],
                'subtype' => $demographicData['subtype'],
                'name' => $demographicData['name'],
            ]);

            if ($demographic == null) {
                $workday->demographics()->create([
                    'type' => $demographicData['type'],
                    'subtype' => $demographicData['subtype'],
                    'name' => $demographicData['name'],
                    'amount' => $demographicData['amount'],
                ]);
            } else {
                $demographic->update(['amount' => $demographicData['amount']]);
            }
        }
    }

    public function workdayable()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function demographics(): HasMany
    {
        return $this->hasMany(WorkdayDemographic::class);
    }

    public function getReadableCollectionAttribute(): ?string
    {
        if (empty($this->collection)) {
            return null;
        }

        return data_get(array_merge(static::$projectCollections, static::$siteCollections), $this->collection, 'Unknown');
    }
}
