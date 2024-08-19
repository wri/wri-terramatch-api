<?php

namespace App\Models\V2;

use App\Helpers\UrlHelper;
use App\Models\Device;
use App\Models\ElevatorVideo;
use App\Models\FilterRecord;
use App\Models\Framework;
use App\Models\Notification;
use App\Models\OfferContact;
use App\Models\Organisation;
use App\Models\Programme;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Traits\HasUuid;
use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\Forms\Application;
use App\Models\V2\Organisation as V2Organisation;
use App\Models\V2\Projects\Project;
use Database\Factories\V2\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property Role primaryRole
 * @property bool isAdmin
 */
class User extends Authenticatable implements JWTSubject
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;
    use InvitedAcceptedAndVerifiedScopesTrait;
    use HasFactory;
    use HasUuid;
    use Searchable;
    use SoftDeletes;
    use HasRoles;

    protected $guard_name = 'api';

    protected static array $adminRoles;

    public $fillable = [
        'organisation_id',
        'first_name',
        'last_name',
        'email_address',
        'password',
        'email_address_verified_at',
        'last_logged_in_at',
        'job_role',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'avatar',
        'phone_number',
        'whatsapp_phone',
        'is_subscribed',
        'has_consented',
        'banners',
        'api_key',
        'country',
        'program',
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

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    protected static function adminRoles()
    {
        if (! empty(self::$adminRoles)) {
            return self::$adminRoles;
        }

        self::$adminRoles = collect(array_keys(config('wri.permissions.roles')))
            ->filter(fn ($roleName) => Str::startsWith($roleName, 'admin'))
            ->toArray();

        return self::$adminRoles;
    }

    public function toSearchableArray()
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email_address,
            'organisation_names' => implode('|', $this->organisations()->pluck('name')->toArray()),
        ];
    }

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
        return $query->role('project-developer');
    }

    public function scopeUserOrTerrafundAdmin(Builder $query): Builder
    {
        return $query->role(['project-developer', 'admin-terrafund']);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->role(self::adminRoles());
    }

    public function scopeOrganisationUuid(Builder $query, string $uuid): Builder
    {
        return $query->whereHas('organisation', function (Builder $query) use ($uuid) {
            return $query->where('uuid', $uuid);
        });
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

    public function getPrimaryRoleAttribute()
    {
        return $this->roles()->first();
    }

    public function organisationsConfirmed(): BelongsToMany
    {
        return $this->belongsToMany(V2Organisation::class)->wherePivot('status', 'approved');
    }

    public function getMyOrganisationAttribute(): ?V2Organisation
    {
        return V2Organisation::find($this->organisation_id);
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

    public function getMyApplicationsAttribute(): Collection
    {
        $orgUuids = collect($this->all_my_organisations)->pluck('uuid')->toArray();

        return Application::whereIn('organisation_uuid', $orgUuids)->get();
    }

    public function getMyFrameworksAttribute(): Collection
    {
        if ($this->is_admin) {
            $permissions = $this->getPermissionsViaRoles();
            $frameworkPermissions = $permissions->filter(function ($permission) {
                return Str::startsWith($permission->name, 'framework-');
            });
            $frameworkSlugs = $frameworkPermissions->map(function ($permission) {
                return Str::after($permission->name, 'framework-');
            });
        } else {
            $frameworkSlugs = $this->projects()->distinct('framework_key')->pluck('framework_key');
        }

        return Framework::whereIn('slug', $frameworkSlugs)->get(['slug', 'name']);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function elevatorVideos()
    {
        return $this->hasMany(ElevatorVideo::class);
    }

    public function filterRecords()
    {
        return $this->hasMany(FilterRecord::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function offerContacts()
    {
        return $this->hasMany(OfferContact::class);
    }

    public function programmes()
    {
        return $this->belongsToMany(Programme::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'v2_project_users');
    }

    public function managedProjects()
    {
        return $this->projects()->wherePivot('is_managing', true);
    }

    public function terrafundProgrammes()
    {
        return $this->belongsToMany(TerrafundProgramme::class);
    }

    public function frameworks()
    {
        return $this->belongsToMany(Framework::class);
    }

    public function wipeData()
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

    public function getFullNameAttribute(): string
    {
        if (empty($this->first_name) && empty($this->last_name)) {
            return 'Unnamed User';
        } elseif (empty($this->first_name)) {
            return $this->last_name;
        } elseif (empty($this->last_name)) {
            return $this->first_name;
        }

        return $this->first_name . ' ' . $this->last_name;
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasAnyRole(self::adminRoles());
    }
}
