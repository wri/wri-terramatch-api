<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundNursery extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'name',
        'start_date',
        'end_date',
        'terrafund_programme_id',
        'seedling_grown',
        'planting_contribution',
        'nursery_type',
        'skip_submission_cycle',
    ];

    protected $with = ['terrafundTreeSpecies', 'terrafundFiles'];

    public function terrafundTreeSpecies()
    {
        return $this->morphMany(TerrafundTreeSpecies::class, 'treeable');
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function terrafundDueSubmissions()
    {
        return $this->morphMany(TerrafundDueSubmission::class, 'terrafund_due_submissionable');
    }

    public function terrafundNurserySubmissions()
    {
        return $this->hasMany(TerrafundNurserySubmission::class);
    }
}
