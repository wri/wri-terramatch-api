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
    public const COLLECTION_PROFIT_BUDGET = 'profit-budget';

    public static $collections = [
        self::COLLECTION_REVENUE => 'Revenue',
        self::COLLECTION_PROFIT_BUDGET => 'Profit Budget',
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
