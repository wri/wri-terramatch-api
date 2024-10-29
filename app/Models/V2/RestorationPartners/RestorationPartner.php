<?php

namespace App\Models\V2\RestorationPartners;

use App\Models\Interfaces\HandlesLinkedFieldSync;
use App\Models\Traits\HasDemographics;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationPartner extends Model implements HandlesLinkedFieldSync
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasDemographics;

    public const COLLECTION_PROJECT_DIRECT_INCOME = 'direct-income';
    public const COLLECTION_PROJECT_INDIRECT_INCOME = 'indirect-income';
    public const COLLECTION_PROJECT_DIRECT_BENEFITS = 'direct-benefits';
    public const COLLECTION_PROJECT_INDIRECT_BENEFITS = 'indirect-benefits';
    public const COLLECTION_PROJECT_DIRECT_CONSERVATION_PAYMENTS = 'direct-conservation-payments';
    public const COLLECTION_PROJECT_INDIRECT_CONSERVATION_PAYMENTS = 'indirect-conservation-payments';
    public const COLLECTION_PROJECT_DIRECT_MARKET_ACCESS = 'direct-market-access';
    public const COLLECTION_PROJECT_INDIRECT_MARKET_ACCESS = 'indirect-market-access';
    public const COLLECTION_PROJECT_DIRECT_CAPACITY = 'direct-capacity';
    public const COLLECTION_PROJECT_INDIRECT_CAPACITY = 'indirect-capacity';
    public const COLLECTION_PROJECT_DIRECT_TRAINING = 'direct-training';
    public const COLLECTION_PROJECT_INDIRECT_TRAINING = 'indirect-training';
    public const COLLECTION_PROJECT_DIRECT_LAND_TITLE = 'direct-land-title';
    public const COLLECTION_PROJECT_INDIRECT_LAND_TITLE = 'indirect-land-title';
    public const COLLECTION_PROJECT_DIRECT_LIVELIHOODS = 'direct-livelihoods';
    public const COLLECTION_PROJECT_INDIRECT_LIVELIHOODS = 'indirect-livelihoods';
    public const COLLECTION_PROJECT_DIRECT_PRODUCTIVITY = 'direct-productivity';
    public const COLLECTION_PROJECT_INDIRECT_PRODUCTIVITY = 'indirect-productivity';
    public const COLLECTION_PROJECT_DIRECT_OTHER = 'direct-other';
    public const COLLECTION_PROJECT_INDIRECT_OTHER = 'indirect-other';

    public const PROJECT_COLLECTIONS = [
        self::COLLECTION_PROJECT_DIRECT_INCOME => 'Direct Income',
        self::COLLECTION_PROJECT_INDIRECT_INCOME => 'Indirect Income',
        self::COLLECTION_PROJECT_DIRECT_BENEFITS => 'Direct In-kind Benefits',
        self::COLLECTION_PROJECT_INDIRECT_BENEFITS => 'Indirect In-kind Benefits',
        self::COLLECTION_PROJECT_DIRECT_CONSERVATION_PAYMENTS => 'Direct Conservation Agreement Payments',
        self::COLLECTION_PROJECT_INDIRECT_CONSERVATION_PAYMENTS => 'Indirect Conservation Agreement Payments',
        self::COLLECTION_PROJECT_DIRECT_MARKET_ACCESS => 'Direct Increased Market Access',
        self::COLLECTION_PROJECT_INDIRECT_MARKET_ACCESS => 'Indirect Increased Market Access',
        self::COLLECTION_PROJECT_DIRECT_CAPACITY => 'Direct Increased Capacity',
        self::COLLECTION_PROJECT_INDIRECT_CAPACITY => 'Indirect Increased Capacity',
        self::COLLECTION_PROJECT_DIRECT_TRAINING => 'Direct Training',
        self::COLLECTION_PROJECT_INDIRECT_TRAINING => 'Indirect Training',
        self::COLLECTION_PROJECT_DIRECT_LAND_TITLE => 'Direct Newly Secured Land Title',
        self::COLLECTION_PROJECT_INDIRECT_LAND_TITLE => 'Indirect Newly Secured Land Title',
        self::COLLECTION_PROJECT_DIRECT_LIVELIHOODS => 'Direct Traditional Livelihoods or Customer Rights',
        self::COLLECTION_PROJECT_INDIRECT_LIVELIHOODS => 'Indirect Traditional Livelihoods or Customer Rights',
        self::COLLECTION_PROJECT_DIRECT_PRODUCTIVITY => 'Direct Increased Productivity',
        self::COLLECTION_PROJECT_INDIRECT_PRODUCTIVITY => 'Indirect Increased Productivity',
        self::COLLECTION_PROJECT_DIRECT_OTHER => 'Direct Other',
        self::COLLECTION_PROJECT_INDIRECT_OTHER => 'Indirect Other',
    ];

    protected $fillable = [
        'partnerable_type',
        'partnerable_id',
        'collection',
        'description',
        'hidden',
    ];

    protected $casts = [
        'hidden' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function partnerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getReadableCollectionAttribute(): ?string
    {
        if (empty($this->collection)) {
            return null;
        }

        return data_get(static::PROJECT_COLLECTIONS, $this->collection, 'Unknown');
    }
}
