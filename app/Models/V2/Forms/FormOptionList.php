<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormOptionList extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    protected $guarded = [];

    public function options(): HasMany
    {
        return $this->hasMany(FormOptionListOption::class);
    }
}
