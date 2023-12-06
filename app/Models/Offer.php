<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Services\Search\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use SearchScopeTrait;
    use SetAttributeByUploadTrait;
    use NamedEntityTrait;

    public $fillable = [
        'organisation_id',
        'name',
        'description',
        'land_types',
        'land_ownerships',
        'land_size',
        'land_continent',
        'land_country',
        'restoration_methods',
        'restoration_goals',
        'funding_sources',
        'funding_amount',
        'price_per_tree',
        'long_term_engagement',
        'reporting_frequency',
        'reporting_level',
        'sustainable_development_goals',
        'cover_photo',
        'video',
        'funding_bracket',
        'visibility',
        'visibility_updated_at',
    ];

    public $casts = [
        'land_types' => 'array',
        'land_ownerships' => 'array',
        'restoration_methods' => 'array',
        'restoration_goals' => 'array',
        'funding_sources' => 'array',
        'sustainable_development_goals' => 'array',
        'long_term_engagement' => 'boolean',
    ];

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload('cover_photo', $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload('video', $video);
    }

    public function organisation()
    {
        return $this->belongsTo(\App\Models\Organisation::class);
    }

    public function offerContacts()
    {
        return $this->hasMany(OfferContact::class);
    }

    public function offerDocuments()
    {
        return $this->hasMany(OfferDocument::class);
    }
}
