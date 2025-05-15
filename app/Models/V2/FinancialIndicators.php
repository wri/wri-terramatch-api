<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FinancialIndicators extends Model implements MediaModel
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
    ];

    public $casts = [
        'amount' => 'float',
        'year' => 'float',
    ];

    public $fileConfiguration = [
        'documentation' => [
            'validation' => 'general-documents',
            'multiple' => false,
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
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
