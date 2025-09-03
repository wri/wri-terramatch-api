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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class DisturbanceReport extends Model implements MediaModel, ReportModel, AuditableContract
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasReportStatus;
    use HasUpdateRequests;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasEntityResources;
    use Auditable;
    use BelongsToThroughTrait;

    protected $table = 'disturbance_reports';

    protected $fillable = [
        'status',
        'date_of_incident',
        'intensity',
        'title',
        'update_request_status',
        'submitted_at',
        'due_at',
        'feedback',
        'feedback_fields',
        'answers',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'date_of_incident' => 'date',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    public $shortName = 'disturbance-report';

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

    /**
     * Polymorphic relation (any model can have disturbance reports).
     */
    public function disturbanceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getAuditableNameAttribute(): string
    {
        return "Disturbance Report #{$this->id}";
    }
}
