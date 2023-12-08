<?php

namespace App\Models;

use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class MediaUpload extends Model
{
    use SetAttributeByUploadTrait;

    public $fillable = [
        'programme_id',
        'site_id',
        'media_title',
        'is_public',
        'upload',
        'location_long',
        'location_lat',
    ];

    public $casts = [
        'is_public' => 'boolean',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function setUploadAttribute($upload): void
    {
        $this->setAttributeByUpload('upload', $upload);
    }
}
