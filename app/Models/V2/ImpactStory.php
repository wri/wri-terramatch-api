<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImpactStory extends Model implements MediaModel
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasUuid;

    protected $fillable = [
      'title',
      'status',
      'organization_id',
      'date',
      'category',
      'thumbnail',
      'content',
  ];

    protected $casts = [
      'category' => 'array',
      'content' => 'array',
    ];

    public $fileConfiguration = [
      'thumbnail' => [
        'validation' => 'logo-image',
        'multiple' => false,
      ],
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(100)
            ->height(100)
            ->sharpen(10);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function organization()
    {
        return $this->belongsTo(Organisation::class);
    }
}
