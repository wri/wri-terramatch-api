<?php

namespace App\Models\V2;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class FundingType extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public $table = 'v2_funding_types';

    protected $fillable = [
        'organisation_id',
        'source',
        'amount',
        'year',
        'type',
        'financial_report_id',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'uuid');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Sync linked-field relation rows for FundingType against an entity's relation.
     */
    public static function syncRelation(Model $entity, string $property, string $inputType, $data, bool $hidden, ?bool $isApproval): void
    {
        if (empty($data)) {
            $entity->$property()->delete();

            return;
        }

        $rows = $data instanceof Collection ? $data : collect($data);

        $relation = $entity->$property();

        foreach ($rows as $entry) {
            $uuid = data_get($entry, 'uuid');

            $orgUuid = data_get($entry, 'organisation_id');
            $financialReportId = data_get($entry, 'financial_report_id');

            if ($entity instanceof FinancialReport) {
                $orgUuid = $orgUuid ?: $entity->organisation?->uuid;
                $financialReportId = $financialReportId ?: $entity->id;
            } elseif ($entity instanceof Organisation) {
                $orgUuid = $orgUuid ?: $entity->uuid;
                $financialReportId = null;
            }

            $payload = [
                'organisation_id' => $orgUuid,
                'source' => data_get($entry, 'source'),
                'amount' => data_get($entry, 'amount'),
                'year' => data_get($entry, 'year'),
                'type' => data_get($entry, 'type'),
                'financial_report_id' => $financialReportId,
            ];

            if (! empty($uuid)) {
                $existing = null;

                if ($entity instanceof FinancialReport) {
                    $existing = static::where('uuid', $uuid)
                        ->where('financial_report_id', $entity->id)
                        ->first();
                } elseif ($entity instanceof Organisation) {
                    $existing = static::where('uuid', $uuid)
                        ->where('organisation_id', $entity->uuid)
                        ->whereNull('financial_report_id')
                        ->first();
                }

                if ($existing) {
                    $existing->update($payload);

                    continue;
                }
            }

            // Check for duplicates based on content, not just UUID
            $duplicate = $relation->where('source', $payload['source'])
                ->where('amount', (int) $payload['amount'])
                ->where('year', (int) $payload['year'])
                ->where('type', $payload['type'])
                ->first();

            if ($duplicate) {
                $duplicate->update($payload);
                continue;
            }

            $relation->create($payload);
        }

        if ($isApproval) {
            $organisation = $entity->organisation;
            if ($organisation) {
                FundingType::where('organisation_id', $organisation->uuid)
                    ->whereNull('financial_report_id')
                    ->delete();

                foreach ($rows as $entry) {
                    FundingType::create([
                        'organisation_id' => $organisation->uuid,
                        'source' => data_get($entry, 'source'),
                        'amount' => data_get($entry, 'amount'),
                        'year' => data_get($entry, 'year'),
                        'type' => data_get($entry, 'type'),
                        'financial_report_id' => null,
                    ]);
                }
            }
        }
    }
}
