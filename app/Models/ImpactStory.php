<?php

namespace App\Models;

use App\Models\V2\Organisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpactStory extends Model
{
    use HasFactory;

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

    public function organization()
    {
        return $this->belongsTo(Organisation::class);
    }
}
