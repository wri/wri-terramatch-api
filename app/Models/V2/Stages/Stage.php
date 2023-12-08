<?php

namespace App\Models\V2\Stages;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\V2\Forms\Form;
use App\Models\V2\FundingProgramme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stage extends Model
{
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use SoftDeletes;

    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';

    public static $statuses = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISABLED => 'Disabled',
    ];

    protected $fillable = [
        'name',
        'order',
        'status',
        'deadline_at',
        'funding_programme_id',
    ];

    protected $with = ['forms'];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function fundingProgramme(): BelongsTo
    {
        return $this->belongsTo(FundingProgramme::class, 'funding_programme_id', 'uuid');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'stage_id', 'uuid');
    }

    public function form(): HasOne
    {
        return $this->hasOne(Form::class, 'stage_id', 'uuid');
    }

    public function nextStage(): HasOne
    {
        return $this->hasOne(Stage::class, 'funding_programme_id', 'funding_programme_id')
            ->where('order', $this->order + 1);
    }

    public function previousStage(): HasOne
    {
        return $this->hasOne(Stage::class, 'funding_programme_id', 'funding_programme_id')
            ->where('order', $this->order - 1);
    }
}
