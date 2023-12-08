<?php

namespace App\Models\V2\BaselineMonitoring;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProjectMetric extends Model implements HasMedia
{
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use InteractsWithMedia;

    public const STATUS_TEMP = 'temp';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public static $statuses = [
        self::STATUS_TEMP => 'Temporary',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    public $table = 'baseline_monitoring_metrics_project';

    protected $fillable = [
        'uuid',
        'monitorable_type',
        'monitorable_id',
        'status',

        'total_hectares',
        'ha_mangrove',
        'ha_assisted',
        'ha_agroforestry',
        'ha_reforestation',
        'ha_peatland',
        'ha_riparian',
        'ha_enrichment',
        'ha_nucleation',
        'ha_silvopasture',
        'ha_direct',

        'tree_count',
        'tree_cover',
        'tree_cover_loss',
        'carbon_benefits',
        'number_of_esrp',

        'field_tree_count',
        'field_tree_count',
        'field_tree_regenerated',
        'field_tree_survival_percent',
    ];

    public $casts = [
        'total_hectares' => 'float',
        'ha_mangrove' => 'float',
        'ha_assisted' => 'float',
        'ha_agroforestry' => 'float',
        'ha_reforestation' => 'float',
        'ha_peatland' => 'float',
        'ha_riparian' => 'float',
        'ha_enrichment' => 'float',
        'ha_nucleation' => 'float',
        'ha_silvopasture' => 'float',
        'ha_direct' => 'float',

        'tree_count' => 'float',
        'tree_cover' => 'float',
        'tree_cover_loss' => 'float',
        'carbon_benefits' => 'float',
        'number_of_esrp' => 'float',

        'field_tree_count' => 'float',
        'field_tree_count' => 'float',
    ];

    public function monitorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCoverImageUrlAttribute(): string
    {
        $coverImage = $this->getMedia('cover')->first();
        if (empty($coverImage)) {
            return '';
        }

        return $coverImage->getFullUrl();
    }

    public function getReportPdfFileAttribute(): string
    {
        $pdf = $this->getMedia('reportPDF')->first();
        if (empty($pdf)) {
            return '';
        }

        return $pdf->getFullUrl();
    }

    public function getGalleryFilesAttribute(): array
    {
        $gallery = [];
        foreach ($this->getMedia('gallery') as $item) {
            $gallery[] = $this->mapMedia($item);
        }

        return $gallery;
    }

    public function getSupportFilesAttribute(): array
    {
        $support = [];
        foreach ($this->getMedia('support') as $item) {
            $support[] = $this->mapMedia($item);
        }

        return $support;
    }

    private function mapMedia($item): array
    {
        return [
            'uuid' => $item->uuid,
            'name' => $item->name,
            'url' => $item->getFullUrl(),
            'mime' => $item->mime_type,
            'size' => $item->size,
            'human_readable_size' => $item->human_readable_size,
        ];
    }
}
