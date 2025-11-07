<?php

namespace App\Models\V2;

use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Forms\Form;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class FinancialReport extends Model implements MediaModel, ReportModel, AuditableContract, AuditableModel
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
    use Auditable;

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

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $fileConfiguration = [];

    public const FINANCIAL_FORM_TYPE = 'financial-report';

    public $table = 'financial_reports';

    public $shortName = 'financial-report';

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

    public function fundingTypes(): HasMany
    {
        return $this->hasMany(FundingType::class, 'financial_report_id', 'id');
    }

    public function updateFinancialDocumentationToOrganisation(): void
    {
        $financialCollection = $this->financialCollection->where('collection', FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS);
        $organisation = $this->organisation();

        if ($organisation) {

            foreach ($financialCollection as $entry) {
                $orgIndicator = FinancialIndicators::where(
                    [
                        'organisation_id' => $this->organisation_id,
                        'year' => $entry->year,
                        'collection' => FinancialIndicators::COLLECTION_NOT_COLLECTION_DOCUMENTS,
                        'financial_report_id' => null,
                    ]
                )->first();

                if (! empty($entry->uuid) && $orgIndicator) {
                    $mediaItems = $entry->getMedia('documentation');

                    // Get existing files to avoid duplicates
                    $existingFiles = $orgIndicator->getMedia('documentation')
                        ->whereIn('file_name', $mediaItems->pluck('file_name'))
                        ->whereIn('size', $mediaItems->pluck('size'));

                    foreach ($mediaItems as $media) {
                        $exists = $existingFiles
                            ->where('file_name', $media->file_name)
                            ->where('size', $media->size)
                            ->count() > 0;

                        if (! $exists) {
                            $newMedia = $media->replicate();
                            $newMedia->model_id = $orgIndicator->id;
                            $newMedia->uuid = (string) Str::uuid();
                            $newMedia->save();
                        }
                    }
                }
            }
        }
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }
}
