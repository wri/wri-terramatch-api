<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ProjectPipeline extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'project_pipeline';

    protected $fillable = [
        'name',
        'submitted_by',
        'description',
        'program',
        'cohort',
        'publish_for',
        'url'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
