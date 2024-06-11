<?php

namespace App\Models\V2\AuditStatus;

use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\MediaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AuditStatus extends Model implements MediaModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasV2MediaCollections;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'status',
        'comment',
        'first_name',
        'last_name',
        'type',
        'is_submitted',
        'is_active',
        'request_removed',
        'date_created',
        'created_by',
    ];

    public $fileConfiguration = [
        'attachments' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
