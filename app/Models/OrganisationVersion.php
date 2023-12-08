<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;
    use IsVersion;
    use HasFactory;

    protected $parentClass = \App\Models\Organisation::class;

    public $fillable = [
        'organisation_id',
        'status',
        'account_type',
        'name',
        'description',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip_code',
        'country',
        'phone_number',
        'website',
        'rejected_reason',
        'approved_rejected_by',
        'type',
        'category',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'avatar',
        'cover_photo',
        'video',
        'approved_rejected_at',
        'founded_at',
        'rejected_reason_body',
        'full_time_permanent_employees',
        'seasonal_employees',
        'part_time_permanent_employees',
        'percentage_female',
        'percentage_youth',
        'revenues_19',
        'revenues_20',
        'revenues_21',
        'community_engagement_strategy',
        'three_year_community_engagement',
        'women_farmer_engagement',
        'young_people_engagement',
        'monitoring_and_evaluation_experience',
        'community_follow_up',
        'total_hectares_restored',
        'hectares_restored_three_years',
        'total_trees_grown',
        'tree_survival_rate',
        'tree_maintenance_and_aftercare',
        'key_contact',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
        'founded_at' => 'datetime',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeByUpload('avatar', $avatar);
    }

    public function setCoverPhotoAttribute($coverPhoto): void
    {
        $this->setAttributeByUpload('cover_photo', $coverPhoto);
    }

    public function setVideoAttribute($video): void
    {
        $this->setAttributeByUpload('video', $video);
    }

    public function setWebsiteAttribute($website): void
    {
        $this->attributes['website'] = UrlHelper::repair($website);
    }

    public function setFacebookAttribute($facebook): void
    {
        $this->attributes['facebook'] = UrlHelper::repair($facebook);
    }

    public function setTwitterAttribute($twitter): void
    {
        $this->attributes['twitter'] = UrlHelper::repair($twitter);
    }

    public function setInstagramAttribute($instagram): void
    {
        $this->attributes['instagram'] = UrlHelper::repair($instagram);
    }

    public function setLinkedinAttribute($linkedin): void
    {
        $this->attributes['linkedin'] = UrlHelper::repair($linkedin);
    }
}
