<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EditHistory extends Model
{
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use SoftDeletes;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';

    public static $statuses = [
        self::STATUS_REQUESTED => 'Requested',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PUBLISHED => 'Published',
    ];

    protected $fillable = [
        'uuid',
        'projectable_type',
        'projectable_id',
        'project_name',
        'editable_type',
        'editable_id',
        'organisation_id',
        'framework_id',
        'status',
        'content',
        'comments',
        'created_by_user_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function editable(): MorphTo
    {
        return $this->morphTo();
    }

    public function projectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function framework()
    {
        return $this->belongsTo(Framework::class);
    }

    public function getOrganisationNameAttribute(): string
    {
        if (empty($this->organisation) || empty($this->organisation->approved_version)) {
            return '';
        }

        return $this->organisation->approved_version->name;
    }

    public function getFrameworkNameAttribute(): string
    {
        return empty($this->framework) ? '' : $this->framework->name;
    }

    public function getModelTypeAttribute(): string
    {
        if (empty($this->editable_type)) {
            return '';
        }

        $reflection = new \ReflectionClass($this->editable_type);

        return Str::snake($reflection->getShortName());
    }

    public function scopeIsStatus($query, string $status)
    {
        if (! in_array($status, array_keys(self::$statuses))) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeIsInStatus($query, array $statuses)
    {
        if (count($statuses) > 0) {
            return $query->whereIn('status', $statuses);
        }
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $frameworkIds = Framework::whereRaw('LOWER(name) LIKE ?', '%' . strtolower($search) . '%')->pluck('id')->toArray();
            $orgIds = OrganisationVersion::whereRaw('LOWER(name) LIKE ?', '%' . strtolower($search) . '%')->pluck('organisation_id')->toArray();

            if (count($frameworkIds) > 0) {
                $query->orWhereIn('framework_id', $frameworkIds);
            }

            if (count($orgIds) > 0) {
                $query->orWhereIn('organisation_id', $orgIds);
            }

            $query->orWhere(DB::raw('LOWER(project_name)'), 'LIKE', '%' . strtolower($search) . '%');
        });
    }
}
