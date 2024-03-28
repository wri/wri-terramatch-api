<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\V2\CriteriaSite;

class CodeCriteria extends Model
{
    use SoftDeletes;

    protected $table = 'code_criteria';

    protected $fillable = [
        'uuid', 'uuid_primary', 'name', 'description', 'is_active'
    ];
    public function criteriaSites()
    {
        return $this->hasMany(CriteriaSite::class, 'criteria_id', 'id');
    }
}
