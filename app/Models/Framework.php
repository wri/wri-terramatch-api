<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Framework extends Model
{
    use HasFactory;
    use HasUuid;

    public $fillable = [
        'name',
        'slug',
        'access_code',
        'project_form_uuid',
        'project_report_form_uuid',
        'site_form_uuid',
        'site_report_form_uuid',
        'nursery_form_uuid',
        'nursery_report_form_uuid',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function projectForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'project_form_uuid');
    }

    public function projectReportForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'project_report_form_uuid');
    }

    public function siteForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'site_form_uuid');
    }

    public function siteReportForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'site_report_form_uuid');
    }

    public function nurseryForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'nursery_form_uuid');
    }

    public function nurseryReportForm(): HasOne
    {
        return $this->hasOne(Form::class, 'uuid', 'nursery_report_form_uuid');
    }

    public function getTotalProjectsCountAttribute()
    {
        return Project::where('framework_key', $this->slug)->count();
    }
}
