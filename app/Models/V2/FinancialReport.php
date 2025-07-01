<?php

namespace App\Models\V2;

use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    protected $fillable = [
        'status',
        'update_request_status',
        'year_of_report',
        'due_at',
        'organisation_id',
        'title',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'year_of_report' => 'integer',
    ];

    public $fileConfiguration = [];

    public $table = 'financial_reports';

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

    public function parentEntity(): BelongsTo
    {
        return $this->organisation();
    }

    public function getForm(): \App\Models\V2\Forms\Form
    {
        $form = \App\Models\V2\Forms\Form::where('uuid', '3489fe71-abbc-4b9f-9cfd-ef3a1903971f')
            ->first();

        if (! $form) {
            throw new \RuntimeException('No form found for FinancialReport without a framework_key');
        }

        return $form;
    }
}
