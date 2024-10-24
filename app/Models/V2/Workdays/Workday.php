<?php

namespace App\Models\V2\Workdays;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\EntityModel;
use Illuminate\Database\Eloquent\Builder;
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

    public const DEMOGRAPHICS_COUNT_CUTOFF = '2024-07-05';

    protected $casts = [
        'published' => 'boolean',
        'hidden' => 'boolean',
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
        'description',
        'hidden',
    ];

    public const COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS = 'paid-nursery-operations';
    public const COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT = 'paid-project-management';
    public const COLLECTION_PROJECT_PAID_OTHER = 'paid-other-activities';
    public const COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS = 'volunteer-nursery-operations';
    public const COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT = 'volunteer-project-management';
    public const COLLECTION_PROJECT_VOLUNTEER_OTHER = 'volunteer-other-activities';
    public const COLLECTION_PROJECT_DIRECT = 'direct';
    public const COLLECTION_PROJECT_CONVERGENCE = 'convergence';

    public const PROJECT_COLLECTION = [
        self::COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS => 'Paid Nursery Operations',
        self::COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT => 'Paid Project Management',
        self::COLLECTION_PROJECT_PAID_OTHER => 'Paid Other Activities',
        self::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS => 'Volunteer Nursery Operations',
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

    public const SITE_COLLECTIONS = [
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

    /**
     * @throws \Exception
     */
    public static function syncRelation(EntityModel $entity, string $property, $data, bool $hidden): void
    {
        if (count($data) == 0) {
            $entity->$property()->delete();

            return;
        }

        // Workdays only have one instance per collection
        $workdayData = $data[0];
        $workday = $entity->$property()->first();
        if ($workday != null && $workday->collection != $workdayData['collection']) {
            throw new \Exception(
                'Workday collection does not match entity property [' .
                'property collection: ' . $workday->collection . ', ' .
                'submitted collection: ' . $workdayData['collection'] . ']'
            );
        }

        if ($workday == null) {
            $workday = Workday::create([
                'workdayable_type' => get_class($entity),
                'workdayable_id' => $entity->id,
                'collection' => $workdayData['collection'],
                'hidden' => $hidden,
            ]);
        } else {
            $workday->update(['hidden' => $hidden]);
        }

        // Make sure the incoming data is clean, and meets our expectations of one row per type/subtype/name combo.
        // The FE is not supposed to send us data with duplicates, but there has been a bug in the past that caused
        // this problem, so this extra check is just covering our bases.
        $syncData = isset($workdayData['demographics']) ? collect($workdayData['demographics'])->reduce(function ($syncData, $row) {
            $type = data_get($row, 'type');
            $subtype = data_get($row, 'subtype');
            $name = data_get($row, 'name');
            $amount = data_get($row, 'amount');

            foreach ($syncData as &$syncRow) {
                if (data_get($syncRow, 'type') === $type &&
                    data_get($syncRow, 'subtype') === $subtype &&
                    data_get($syncRow, 'name') === $name) {

                    // Keep the last value for this type/subtype/name in the incoming data set.
                    $syncRow['amount'] = $amount;

                    return $syncData;
                }
            }

            $syncData[] = $row;

            return $syncData;
        }, []) : [];

        $demographics = $workday->demographics;
        $represented = collect();
        foreach ($syncData as $row) {
            $demographic = $demographics->firstWhere([
                'type' => data_get($row, 'type'),
                'subtype' => data_get($row, 'subtype'),
                'name' => data_get($row, 'name'),
            ]);

            if ($demographic == null) {
                $workday->demographics()->create($row);
            } else {
                $represented->push($demographic->id);
                $demographic->update(['amount' => data_get($row, 'amount')]);
            }
        }
        // Remove any existing demographic that wasn't in the submitted set.
        foreach ($demographics as $demographic) {
            if (! $represented->contains($demographic->id)) {
                $demographic->delete();
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

    public function demographics(): HasMany
    {
        return $this->hasMany(WorkdayDemographic::class);
    }

    public function getReadableCollectionAttribute(): ?string
    {
        if (empty($this->collection)) {
            return null;
        }

        return data_get(array_merge(static::PROJECT_COLLECTION, static::SITE_COLLECTIONS), $this->collection, 'Unknown');
    }
}
