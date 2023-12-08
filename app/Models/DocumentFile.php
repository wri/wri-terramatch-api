<?php

namespace App\Models;

use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentFile extends Model
{
    use SetAttributeByUploadTrait;
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'uuid',
        'document_fileable_type',
        'document_fileable_id',
        'upload',
        'title',
        'collection',
        'is_public',
    ];

    public $casts = [
        'is_public' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = empty($model->uuid) ? Str::uuid() : $model->uuid;
        });
    }

    public function documentFileable()
    {
        return $this->morphTo();
    }

    public function setUploadAttribute($upload): void
    {
        $this->setAttributeByUpload('upload', $upload);
    }
}
