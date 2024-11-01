<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasUuid;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'organisation_uuid',
        'funding_programme_uuid',
        'updated_by',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_uuid', 'uuid');
    }

    public function fundingProgramme(): BelongsTo
    {
        return $this->belongsTo(FundingProgramme::class, 'funding_programme_uuid', 'uuid');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'application_id', 'id');
    }

    public function getProjectPitchUuidAttribute()
    {
        $submission = $this->formSubmissions()->first();

        return empty($submission) ? null : $submission->project_pitch_uuid;
    }

    public function currentSubmission(): HasOne
    {
        return $this->hasOne(FormSubmission::class, 'application_id', 'id')->latestOfMany();
    }

    public function lastUpdatedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

    public function toSearchableArray()
    {
        return [
            'organisation_name' => $this->organisation->name,
        ];
    }

    public static function search($query)
    {
        return self::select('applications.*')
            ->join('organisations', 'applications.organisation_uuid', '=', 'organisations.uuid')
            ->where('organisations.name', 'like', "%$query%");
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeProjectPitchUuid(Builder $query, $projectPitchUuid): Builder
    {
        return $query->whereHas('currentSubmission', function ($qry) use ($projectPitchUuid) {
            $qry->where('project_pitch_uuid', $projectPitchUuid);
        });
    }

    public function scopeCurrentStage(Builder $query, $stageUuid): Builder
    {
        return $query->whereHas('currentSubmission', function ($qry) use ($stageUuid) {
            $qry->where('stage_uuid', $stageUuid);
        });
    }

    public function scopeCurrentSubmissionStatus(Builder $query, $status): Builder
    {
        return $query->whereHas('currentSubmission', function ($qry) use ($status) {
            $qry->where('status', $status);
        });
    }
}
