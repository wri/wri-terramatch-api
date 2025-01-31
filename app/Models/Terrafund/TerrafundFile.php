<?php

namespace App\Models\Terrafund;

use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundFile extends Model
{
    use SetAttributeByUploadTrait;
    use HasFactory;

    public $fillable = [
        'fileable_type',
        'fileable_id',
        'upload',
        'is_public',
        'location_long',
        'location_lat',
        'collection',
    ];

    public $casts = [
        'is_public' => 'boolean',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function setUploadAttribute($upload): void
    {
        $this->setAttributeByUpload('upload', $upload);
    }

    public function scopeTerrafundProgrammeSubmission(Builder $query): Builder
    {
        return $query->where('fileable_type', TerrafundProgrammeSubmission::class);
    }

    public function scopeTerrafundSite(Builder $query): Builder
    {
        return $query->where('fileable_type', TerrafundSite::class);
    }

    public function scopeTerrafundSiteSubmission(Builder $query): Builder
    {
        return $query->where('fileable_type', TerrafundSiteSubmission::class);
    }

    public function scopeTerrafundNursery(Builder $query): Builder
    {
        return $query->where('fileable_type', TerrafundNursery::class);
    }

    public function scopeTerrafundNurserySubmission(Builder $query): Builder
    {
        return $query->where('fileable_type', TerrafundNurserySubmission::class);
    }
}
