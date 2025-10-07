<?php

namespace App\Models\V2;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    }
}
