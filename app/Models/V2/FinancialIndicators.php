<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialIndicators extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public $table = 'financial_indicators';

    protected $fillable = [
        'organisation_id',
        'collection',
        'amount',
        'year',
        'documentation',
        'description',
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
}
