<?php

namespace App\Models\V2;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class DisturbanceReportEntry extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    protected $fillable = [
        'uuid',
        'disturbance_report_id',
        'name',
        'input_type',
        'title',
        'subtitle',
        'value',
    ];

    public function disturbanceReport(): BelongsTo
    {
        return $this->belongsTo(DisturbanceReport::class);
    }

    public function scopeName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    public static function syncRelation(Model $entity, string $property, string $inputType, $data, bool $hidden, ?bool $isApproval): void
    {
        $relation = $entity->$property();

        $rows = collect($data ?? []);
        if ($rows->isNotEmpty() && $rows->first() instanceof \Illuminate\Support\Collection) {
            $rows = $rows->first();
        }

        $existing = $relation->where('disturbance_report_id', $entity->id)->get();
        $existingByUuid = $existing->keyBy('uuid');
        $existingByName = $existing->keyBy('name');

        $processedUuids = collect();
        $processedNames = collect();

        foreach ($rows as $row) {
            $uuid = data_get($row, 'uuid');
            $name = data_get($row, 'name');

            if (empty($name)) {
                continue;
            }

            $payload = [
                'input_type' => data_get($row, 'input_type'),
                'title' => data_get($row, 'title'),
                'subtitle' => data_get($row, 'subtitle'),
                'value' => is_array(data_get($row, 'value')) ? json_encode(data_get($row, 'value')) : data_get($row, 'value'),
            ];

            $model = null;

            if (! empty($uuid)) {
                $model = $existingByUuid->get($uuid);
                $processedUuids->push($uuid);
            }

            if (! $model) {
                $model = $existingByName->get($name);
                $processedNames->push($name);
            }

            if ($model) {
                $model->update($payload);
            } else {
                $createData = array_merge(['name' => $name], $payload);
                if (! empty($uuid)) {
                    $createData['uuid'] = $uuid;
                }
                $relation->create($createData);
            }
        }

        $toDelete = $existing->filter(function ($entry) use ($processedUuids, $processedNames) {
            return ! $processedUuids->contains($entry->uuid) && ! $processedNames->contains($entry->name);
        });

        $toDelete->each->delete();

        // Process approval logic if this is an approval
        if ($isApproval === true) {
            self::processApprovalLogic($entity);
        }
    }

    /**
     * Process the disturbance report entries when the report is approved
     * Creates a Disturbance record and updates affected SitePolygon records
     */
    private static function processApprovalLogic(Model $entity): void
    {
        $entries = $entity->entries()->get();
        $affectedPolygonUuids = collect();
        $disturbanceData = [];

        foreach ($entries as $entry) {
            // Look for entries that contain affected polygon UUIDs
            $polygonFieldNames = ['polygon-affected'];

            if (in_array($entry->name, $polygonFieldNames) && $entry->value) {
                \Log::debug("Processing polygon field: {$entry->name} with value: {$entry->value}");

                try {
                    // Parse as JSON array of arrays containing polygon objects
                    $parsedValue = json_decode($entry->value, true);
                    if (is_array($parsedValue)) {
                        foreach ($parsedValue as $groupIndex => $polygonGroup) {
                            if (is_array($polygonGroup)) {
                                // Handle array of arrays format
                                foreach ($polygonGroup as $polygonObj) {
                                    if (is_array($polygonObj) && isset($polygonObj['polyUuid'])) {
                                        \Log::debug("Adding polygon UUID: {$polygonObj['polyUuid']} ({$polygonObj['polyName']}) from group {$groupIndex}");
                                        $affectedPolygonUuids->push($polygonObj['polyUuid']);
                                    }
                                }
                            } elseif (is_array($polygonGroup) && isset($polygonGroup['polyUuid'])) {
                                // Handle direct object format (fallback)
                                \Log::debug("Adding polygon UUID: {$polygonGroup['polyUuid']} ({$polygonGroup['polyName']})");
                                $affectedPolygonUuids->push($polygonGroup['polyUuid']);
                            } elseif (is_string($polygonGroup) && trim($polygonGroup)) {
                                // Fallback for simple string UUIDs
                                \Log::debug('Adding polygon UUID (string): ' . trim($polygonGroup));
                                $affectedPolygonUuids->push(trim($polygonGroup));
                            }
                        }
                    }
                } catch (\Exception $error) {
                    \Log::warning("Failed to parse polygon JSON: {$error->getMessage()}, trying comma-separated values");
                    // If JSON parsing fails, try comma-separated values
                    $uuids = array_filter(array_map('trim', explode(',', $entry->value)));
                    foreach ($uuids as $uuid) {
                        $affectedPolygonUuids->push($uuid);
                    }
                }
            }

            // Process other disturbance data fields
            switch ($entry->name) {
                case 'intensity':
                    if ($entry->value) {
                        $disturbanceData['intensity'] = $entry->value;
                    }

                    break;
                case 'extent':
                    if ($entry->value) {
                        $disturbanceData['extent'] = $entry->value;
                    }

                    break;
                case 'disturbance-type':
                    if ($entry->value) {
                        $disturbanceData['type'] = $entry->value;
                    }

                    break;
                case 'disturbance-subtype':
                    if ($entry->value) {
                        $subtype = json_decode($entry->value, true);
                        $disturbanceData['subtype'] = is_array($subtype) ? $subtype : [];
                    }

                    break;
                case 'people-affected':
                    if ($entry->value) {
                        $disturbanceData['people_affected'] = is_numeric($entry->value) ? (int) $entry->value : null;
                    }

                    break;
                case 'monetary-damage':
                    if ($entry->value) {
                        $disturbanceData['monetary_damage'] = is_numeric($entry->value) ? (float) $entry->value : null;
                    }

                    break;
                case 'property-affected':
                    if ($entry->value) {
                        $property = json_decode($entry->value, true);
                        $disturbanceData['property_affected'] = is_array($property) ? $property : [];
                    }

                    break;
                case 'date-of-disturbance':
                    if ($entry->value) {
                        $disturbanceData['disturbance_date'] = $entry->value;
                    }

                    break;
            }
        }

        if ($affectedPolygonUuids->isEmpty()) {
            Log::info("No affected polygons found for disturbance report {$entity->id}, skipping disturbance creation");

            return;
        }

        // Create the disturbance record
        $disturbanceCreateData = [
            'disturbanceable_type' => get_class($entity),
            'disturbanceable_id' => $entity->id,
            'disturbance_date' => $disturbanceData['disturbance_date'] ?? null,
            'type' => $disturbanceData['type'] ?? null,
            'subtype' => $disturbanceData['subtype'] ?? null,
            'intensity' => $disturbanceData['intensity'] ?? null,
            'extent' => $disturbanceData['extent'] ?? null,
            'people_affected' => $disturbanceData['people_affected'] ?? null,
            'monetary_damage' => $disturbanceData['monetary_damage'] ?? null,
            'property_affected' => $disturbanceData['property_affected'] ?? null,
            'description' => $entity->description,
            'action_description' => $entity->action_description,
            'hidden' => false,
        ];

        $disturbance = Disturbance::create($disturbanceCreateData);

        // Find all affected site polygons and validate they're not already affected by another disturbance
        $affectedPolygons = SitePolygon::whereIn('uuid', $affectedPolygonUuids->unique()->toArray())
            ->where('is_active', true)
            ->get();

        // Check for polygons that are already affected by another disturbance
        $alreadyAffectedPolygons = $affectedPolygons->filter(function ($polygon) {
            return $polygon->disturbance_id !== null;
        });

        if ($alreadyAffectedPolygons->isNotEmpty()) {
            $alreadyAffectedUuids = $alreadyAffectedPolygons->pluck('uuid')->join(', ');
            Log::warning("The following polygons are already affected by another disturbance: {$alreadyAffectedUuids}");
        }

        // Update all affected polygons with the disturbance_id, where they are not already affected by another disturbance
        SitePolygon::whereIn('uuid', $affectedPolygonUuids->unique()->toArray())
            ->where('is_active', true)
            ->whereNull('disturbance_id')
            ->update(['disturbance_id' => $disturbance->id]);

        Log::info("Created disturbance {$disturbance->id} for report {$entity->id} affecting " . $affectedPolygonUuids->unique()->count() . ' polygons');
    }
}
