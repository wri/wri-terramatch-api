<?php

namespace App\Models\V2;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\V2\FinancialReport;

class FinancialIndicators extends Model implements MediaModel, HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use InteractsWithMedia;
    use HasV2MediaCollections;

    public $table = 'financial_indicators';

    protected $fillable = [
        'organisation_id',
        'collection',
        'amount',
        'year',
        'description',
        'financial_report_id',
        'exchange_rate',
    ];

    public $casts = [
        'amount' => 'float',
        'year' => 'float',
    ];

    public $fileConfiguration = [
        'documentation' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public const COLLECTION_REVENUE = 'revenue';
    public const COLLECTION_BUDGET = 'budget';
    public const COLLECTION_EXPENSES = 'expenses';
    public const COLLECTION_CURRENT_ASSETS = 'current-assets';
    public const COLLECTION_CURRENT_LIABILITIES = 'current-liabilities';
    public const COLLECTION_PROFIT = 'profit';
    public const COLLECTION_CURRENT_RATIO = 'current-ratio';
    public const COLLECTION_NOT_COLLECTION_DOCUMENTS = 'description-documents';

    public static $collections = [
        self::COLLECTION_REVENUE => 'Revenue',
        self::COLLECTION_BUDGET => 'Budget',
        self::COLLECTION_EXPENSES => 'Expenses',
        self::COLLECTION_CURRENT_ASSETS => 'Current Assets',
        self::COLLECTION_CURRENT_LIABILITIES => 'Current Liabilities',
        self::COLLECTION_PROFIT => 'Profit',
        self::COLLECTION_CURRENT_RATIO => 'Current Ratio',
    ];

    /**
     * Sync financial indicators data with the entity
     */
    public static function syncRelation(Model $entity, string $property, string $inputType, $data, bool $hidden): void
    {
        if (empty($data)) {
            $entity->$property()->delete();
            return;
        }

        $firstRecord = $data[0];
        $startMonth = $firstRecord['start_month'] ?? null;
        $currency = $firstRecord['currency'] ?? null;
        $organisationId = $firstRecord['organisation_id'] ?? null;
        $financialReport = null;
        if ($firstRecord['financial_report_id']) {
            $financialReport =  FinancialReport::isUuid($firstRecord['financial_report_id'])->first();
        }


        $newUuids = collect($data)->pluck('uuid')->filter();
        $entity->$property()->whereNotIn('uuid', $newUuids)->delete();

        foreach ($data as $entry) {
            $uuid = $entry['uuid'] ?? null;
            
            if ($uuid) {
                $existing = $entity->$property()->where('uuid', $uuid)->first();
                
                if ($existing) {
                    $existing->update([
                        'collection' => $entry['collection'],
                        'amount' => $entry['amount'],
                        'year' => $entry['year'],
                        'description' => $entry['description'],
                        'exchange_rate' => $entry['exchange_rate'],
                    ]);
                } else {
                    $entity->$property()->create([
                        'collection' => $entry['collection'],
                        'amount' => $entry['amount'],
                        'year' => $entry['year'],
                        'description' => $entry['description'],
                        'exchange_rate' => $entry['exchange_rate'],
                        'organisation_id' => $entry['organisation_id'],
                        'financial_report_id' => $financialReport?->id,
                    ]);
                }
            } else {
                $entity->$property()->create([
                    'collection' => $entry['collection'],
                    'amount' => $entry['amount'],
                    'year' => $entry['year'],
                    'description' => $entry['description'],
                    'exchange_rate' => $entry['exchange_rate'],
                    'organisation_id' => $entry['organisation_id'],
                    'financial_report_id' => $financialReport?->id,
                ]);
            }
        }

        if (($startMonth !== null || $currency !== null) && $financialReport) {
            $financialReport->update([
                'fin_start_month' => $startMonth,
                'currency' => $currency,
            ]);
        }
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    public function financialReport()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id', 'id');
    }
    public function getStartMonthAttribute()
    {
        return $this->financialReport?->fin_start_month;
    }

    public function getCurrencyAttribute()
    {
        return $this->financialReport?->currency;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }
}
