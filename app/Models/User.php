<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Traits\HasUuid;
use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\Organisation as V2Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;
    use InvitedAcceptedAndVerifiedScopesTrait;
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasRoles;

    protected $guard_name = 'api';

    public $fillable = [
        'organisation_id',
        'first_name',
        'last_name',
        'email_address',
        'password',
        'email_address_verified_at',
        'role',
        'last_logged_in_at',
        'job_role',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'avatar',
        'phone_number',
        'is_subscribed',
        'has_consented',
        'whatsapp_phone',
        'banners',
        'country',
        'program'
    ];

    protected $casts = [
        'last_logged_in_at' => 'datetime',
        'email_address_verified_at' => 'datetime',
        'is_subscribed' => 'boolean',
        'has_consented' => 'boolean',
    ];

    protected $with = [
        'frameworks',
    ];

    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function setPasswordAttribute(string $password): void
    {
        if (Hash::needsRehash($password)) {
            $password = Hash::make($password);
        }
        $this->attributes['password'] = $password;
    }

    public function getIsPpcUserAttribute(): bool
    {
        $framework = Framework::where('name', 'PPC')->firstOrFail();

        return $this->frameworks->contains($framework->id);
    }

    public function getIsTerrafundUserAttribute(): bool
    {
        $framework = Framework::where('name', 'Terrafund')->firstOrFail();

        return $this->frameworks->contains($framework->id);
    }

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeByUpload('avatar', $avatar);
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

    public function scopeUser(Builder $query): Builder
    {
        return $query->where('role', '=', 'user');
    }

    public function scopeUserOrTerrafundAdmin(Builder $query): Builder
    {
        return $query->where('role', 'user')
            ->orWhere('role', 'terrafund_admin');
    }

    public function scopeUnverified(Builder $query): Builder
    {
        return $query->whereNull('email_address_verified_at');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function v2Organisation(): BelongsTo
    {
        return $this->belongsTo(V2Organisation::class, 'organisation_id', 'id');
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(V2Organisation::class)->withPivot('status');
    }

    public function organisationsRequested(): BelongsToMany
    {
        return $this->belongsToMany(V2Organisation::class)->wherePivot('status', 'requested');
    }

    public function organisationsConfirmed(): BelongsToMany
    {
        return $this->belongsToMany(V2Organisation::class)->wherePivot('status', 'approved');
    }

    public function getAllMyOrganisationsAttribute(): Collection
    {
        $primaryOrganisation = V2Organisation::find($this->organisation_id);
        $organisations = $this->organisations;
        if (! empty($primaryOrganisation)) {
            $organisations->push($primaryOrganisation);
        }

        return $organisations;
    }

    public function getPrimaryRoleAttribute()
    {
        return $this->roles()->first();
    }

    public function getMyPrimaryOrganisationAttribute()
    {
        if (! empty($this->v2Organisation)) {
            return $this->v2Organisation;
        }

        if ($this->organisationsConfirmed()->count() > 0) {
            return $this->organisationsConfirmed()->first();
        }

        if ($this->organisationsRequested()->count() > 0) {
            return $this->organisationsRequested()->first();
        }

        return null;
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'v2_project_users')->withTimestamps();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function elevatorVideos(): HasMany
    {
        return $this->hasMany(ElevatorVideo::class);
    }

    public function filterRecords(): HasMany
    {
        return $this->hasMany(FilterRecord::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function offerContacts(): HasMany
    {
        return $this->hasMany(OfferContact::class);
    }

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class);
    }

    public function terrafundProgrammes(): BelongsToMany
    {
        return $this->belongsToMany(TerrafundProgramme::class);
    }

    public function frameworks(): BelongsToMany
    {
        return $this->belongsToMany(Framework::class);
    }

    public function wipeData(): void
    {
        $this->email_address = Str::uuid();
        $this->phone_number = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->password = Str::random(12);
        $this->email_address_verified_at = null;
        $this->last_logged_in_at = null;
        $this->facebook = null;
        $this->twitter = null;
        $this->linkedin = null;
        $this->instagram = null;
        $this->avatar = null;
    }
}
