<?php

namespace App\Models\V2\Workdays;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasDemographics;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use HasDemographics;

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
        self::COLLECTION_PROJECT_DIRECT => 'Direct Workdays',
        self::COLLECTION_PROJECT_CONVERGENCE => 'Convergence Workdays',
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

    public function workdayable()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getReadableCollectionAttribute(): ?string
    {
        if (empty($this->collection)) {
            return null;
        }

        return data_get(array_merge(static::PROJECT_COLLECTION, static::SITE_COLLECTIONS), $this->collection, 'Unknown');
    }
}
