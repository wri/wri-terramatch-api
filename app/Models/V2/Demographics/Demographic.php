<?php

namespace App\Models\V2\Demographics;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property string uuid
 * @property string demographical_type
 * @property int demographical_id
 * @property string type
 * @property string collection
 * @property string description
 * @property bool hidden
 */
class Demographic extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public const DEMOGRAPHICS_COUNT_CUTOFF = '2024-07-05';

    public const WORKDAY_TYPE = 'workdays';
    public const RESTORATION_PARTNER_TYPE = 'restoration-partners';

    public const VALID_TYPES = [self::WORKDAY_TYPE, self::RESTORATION_PARTNER_TYPE];

    // In TM-1681 we moved several "name" values to "subtype". This check helps make sure that both in-flight
    // work at the time of release, and updates from update requests afterward honor that change.
    protected const SUBTYPE_SWAP_TYPES = [DemographicEntry::GENDER, DemographicEntry::AGE, DemographicEntry::CASTE];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    protected $fillable = [
        'uuid',
        'demographical_type',
        'demographical_id',
        'type',
        'collection',
        'description',
        'hidden',
    ];

    /**
     * @throws \Exception
     */
    public static function syncRelation(EntityModel $entity, string $property, string $inputType, $data, bool $hidden): void
    {
        $morph = $entity->$property();
        if (count($data) == 0) {
            $morph->delete();

            return;
        }

        // Demographic collections only have one instance per collection
        $demographicData = $data[0];
        $demographic = $morph->first();
        if ($demographic != null && $demographic->collection != $demographicData['collection']) {
            throw new \Exception(
                'Collection does not match entity property [' .
                'property collection: ' . $demographic->collection . ', ' .
                'submitted collection: ' . $demographicData['collection'] . ']'
            );
        }

        if ($demographic == null) {
            $demographic = $morph->create([
                'type' => Str::kebab($inputType),
                'collection' => $demographicData['collection'],
                'hidden' => $hidden,
            ]);
        } else {
            $demographic->update(['hidden' => $hidden]);
        }

        // Make sure the incoming data is clean, and meets our expectations of one row per type/subtype/name combo.
        // The FE is not supposed to send us data with duplicates, but there has been a bug in the past that caused
        // this problem, so this extra check is just covering our bases.
        $syncData = isset($demographicData['demographics']) ? collect($demographicData['demographics'])->reduce(function ($syncData, $row) {
            $type = data_get($row, 'type');
            $subtype = data_get($row, 'subtype');
            $name = data_get($row, 'name');
            $amount = data_get($row, 'amount');

            // In TM-1681 we moved several "name" values to "subtype". This check helps make sure that both in-flight
            // work at the time of release, and updates from update requests afterward honor that change.
            if (in_array($type, self::SUBTYPE_SWAP_TYPES) && $name != null && $subtype == null) {
                $subtype = $name;
                $name = null;
            }

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

        $represented = collect();
        foreach ($syncData as $row) {
            $entry = $demographic->entries()->where([
                'type' => data_get($row, 'type'),
                'subtype' => data_get($row, 'subtype'),
                'name' => data_get($row, 'name'),
            ])->first();

            if ($entry == null) {
                $represented->push($demographic->entries()->create($row)->id);
            } else {
                $represented->push($entry->id);
                $entry->update(['amount' => data_get($row, 'amount')]);
            }
        }

        // Remove any existing entry that wasn't in the submitted set.
        $demographic->entries()->whereNotIn('id', $represented)->delete();
    }

    public function demographical()
    {
        return $this->morphTo();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DemographicEntry::class);
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

    public function getReadableCollectionAttribute(): ?string
    {
        if (empty($this->collection)) {
            return 'Unknown';
        }

        $collections = match ($this->type) {
            self::RESTORATION_PARTNER_TYPE => match ($this->demographical_type) {
                ProjectReport::class => DemographicCollections::RESTORATION_PARTNERS_PROJECT_COLLECTIONS,
                default => null
            },
            self::WORKDAY_TYPE => match ($this->demographical_type) {
                ProjectReport::class => DemographicCollections::WORKDAYS_PROJECT_COLLECTIONS,
                SiteReport::class => DemographicCollections::WORKDAYS_SITE_COLLECTIONS,
                default => null
            },
            default => null
        };
        if (empty($collections)) {
            return 'Unknown';
        }

        return data_get($collections, $this->collection, 'Unknown');
    }
}
