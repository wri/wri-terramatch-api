<?php

namespace App\Models\Terrafund;

use App\Models\Interfaces\TerrafundSubmissionInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundProgrammeSubmission extends Model implements TerrafundSubmissionInterface
{
    use HasFactory;
    use SoftDeletes;

    public $guarded = [];

    public function terrafundProgramme()
    {
        return $this->belongsTo(TerrafundProgramme::class);
    }

    public function terrafundDueSubmissionable()
    {
        return  $this->terrafundProgramme()->first();
    }

    public function terrafundDueSubmission()
    {
        return $this->belongsTo(TerrafundDueSubmission::class);
    }

    public function terrafundFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable');
    }

    public function terrafundPhotosFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable')->where('collection', 'photos');
    }

    public function terrafundDocumentsFiles()
    {
        return $this->morphMany(TerrafundFile::class, 'fileable')->where('collection', 'other_additional_documents');
    }

    public function scopeSubmissionsBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('created_at', [$startDate->startOfDay(),$endDate->endOfDay()]);
    }
}
