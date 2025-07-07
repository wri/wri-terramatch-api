<?php

namespace App\Models\V2;

use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialReport extends Model implements MediaModel, ReportModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasReportStatus;
    use HasUpdateRequests;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use UsesLinkedFields;
    use HasEntityResources;
    use BelongsToThroughTrait;

    protected $fillable = [
        'status',
        'update_request_status',
        'year_of_report',
        'due_at',
        'organisation_id',
        'title',
        'feedback',
        'feedback_fields',
        'answers',
        'submitted_at',
        'fin_start_month',
        'currency',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'year_of_report' => 'integer',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    public $fileConfiguration = [];

    public const FINANCIAL_FORM_TYPE = 'financial-report';

    public $table = 'financial_reports';

    public $shortName = 'financial-report';

    // Simple status constants
    public const STATUS_DUE = 'due';
    public const STATUS_STARTED = 'started';
    public const STATUS_SUBMITTED = 'submitted';

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function getAuditableNameAttribute(): string
    {
        return "Financial Report for {$this->year_of_report}";
    }

    public function getParentNameAttribute(): string
    {
        return $this->organisation?->name ?? '';
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentEntity(): BelongsTo
    {
        return $this->organisation();
    }

    public function getForm(): Form
    {
        $form = Form::where('type', self::FINANCIAL_FORM_TYPE)
            ->first();

        if (! $form) {
            throw new \RuntimeException('No form found for FinancialReport without a form type');
        }

        return $form;
    }

    public function financialCollection(): HasMany
    {
        return $this->hasMany(FinancialIndicators::class, 'financial_report_id', 'id');
    }

    public function getFundingTypesAttribute()
    {
        return $this->project?->organisation?->fundingTypes;
    }

    public function submitForApproval(): void
    {
        if (empty($this->submitted_at)) {
            $this->completion = 100;
            $this->submitted_at = now();
        }
        $this->status = self::STATUS_SUBMITTED;
        $this->save();
    }
}
