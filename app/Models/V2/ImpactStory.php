<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpactStory extends Model
{
    use HasFactory;
    use SoftDeletes;
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

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function organization()
    {
        return $this->belongsTo(Organisation::class);
    }
}
