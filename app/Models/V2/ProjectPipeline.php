<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPipeline extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'project_pipeline';

    protected $fillable = [
        'Name',
        'SubmittedBy',
        'Description',
        'Program',
        'Cohort',
        'PublishFor',
        'URL',
        'CreatedDate',
        'ModifiedDate',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
