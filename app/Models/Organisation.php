<?php

namespace App\Models;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasVersions;
    use NamedEntityTrait;
    use HasFactory;
    use HasUuid;

    protected $versionClass = \App\Models\OrganisationVersion::class;

    public $guarded = [];

    public function users()
    {
        return $this->hasMany(\App\Models\V2\User::class);
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function organisationPhotos()
    {
        return $this->hasMany(OrganisationPhoto::class);
    }

    public function organisationFiles()
    {
        return $this->hasMany(OrganisationFile::class);
    }

    public function filterRecords()
    {
        return $this->hasMany(FilterRecord::class);
    }

    public function interests()
    {
        return $this->hasMany(Interest::class);
    }

    public function terrafundProgrammes()
    {
        return $this->hasMany(TerrafundProgramme::class);
    }

    public function financialCollection(): HasMany
    {
        return $this->hasMany(FinancialIndicators::class, 'organisation_id', 'id');
    }
}
