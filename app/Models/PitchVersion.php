<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\User;
use App\Services\Search\SearchScopeTrait;
use Illuminate\Database\Eloquent\Model;

class PitchVersion extends Model implements Version
{
    use NamedEntityTrait;
    use IsVersion;
    use SetAttributeByUploadTrait;
    use SearchScopeTrait;

    protected $parentClass = \App\Models\Pitch::class;

    public $fillable = [
        'pitch_id',
        'status',
        'rejected_reason',
        'approved_rejected_by',
        'approved_rejected_at',
        'name',
        'description',
        'land_types',
        'land_ownerships',
        'land_size',
        'land_continent',
        'land_country',
        'land_geojson',
        'restoration_methods',
        'restoration_goals',
        'funding_sources',
        'funding_amount',
        'revenue_drivers',
        'estimated_timespan',
        'long_term_engagement',
        'reporting_frequency',
        'reporting_level',
        'sustainable_development_goals',
        'cover_photo',
        'video',
        'problem',
        'anticipated_outcome',
        'who_is_involved',
        'local_community_involvement',
        'training_involved',
        'training_type',
        'training_amount_people',
        'people_working_in',
        'people_amount_nearby',
        'people_amount_abroad',
        'people_amount_employees',
        'people_amount_volunteers',
        'benefited_people',
        'future_maintenance',
        'use_of_resources',
        'price_per_tree',
        'rejected_reason_body',
        'funding_bracket',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
        'land_types' => 'array',
        'land_ownerships' => 'array',
        'restoration_methods' => 'array',
        'restoration_goals' => 'array',
        'funding_sources' => 'array',
        'revenue_drivers' => 'array',
        'sustainable_development_goals' => 'array',
        'long_term_engagement' => 'boolean',
    ];

    public function pitch()
    {
        return $this->belongsTo(Pitch::class);
    }

    public function approvedRejectedBy()
    {
        return $this->belongsTo(User::class, 'approved_rejected_by');
    }

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload('cover_photo', $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload('video', $video);
    }

    /**
     * This method stops anyone from using the price_per_tree column on the pitch_versions table. This column is
     * computed every time a tree_species_versions row is approved, and is therefore read only. It should not be updated
     * manually! It is far from ideal to denormalise our database, but this column is required when filtering by
     * price_per_tree. Without it we are required to do subqueries for every row in the pitch_versions table, which is
     * not scalable at all. By calculating the price_per_tree from multiple tree_species_versions once, we almost
     * "cache" it in this column and can then quickly filter by it. This is the best solution I have at the moment which
     * doesn't cripple the database.
     */
    public function setPricePerTreeAttribute($pricePerTree): void
    {
    }
}
