<?php

namespace App\Models\Terrafund;

use App\Models\Interfaces\TerrafundSubmissionInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundNurserySubmission extends Model implements TerrafundSubmissionInterface
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'seedlings_young_trees',
        'interesting_facts',
        'site_prep',
        'terrafund_nursery_id',
        'terrafund_due_submission_id',
        'shared_drive_link',
    ];

    public function terrafundNursery()
    {
        return $this->belongsTo(TerrafundNursery::class);
    }

    public function terrafundDueSubmissionable()
    {
        return  $this->terrafundNursery()->first();
    }

    public function terrafundDueSubmission()
    {
        return $this->belongsTo(TerrafundDueSubmission::class);
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function scopeSubmissionsBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate->startOfDay(),$endDate->endOfDay()]);
    }
}
