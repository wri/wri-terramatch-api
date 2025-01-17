<?php

namespace App\Models\Terrafund;

use App\Models\Draft;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerrafundDueSubmission extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $with = ['drafts','terrafund_due_submissionable'];

    protected $fillable = [
        'is_submitted',
        'terrafund_due_submissionable_type',
        'terrafund_due_submissionable_id',
        'due_at',
        'submitted_at',
        'unable_report_reason',
        'terrafund_programme_id',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        self::updating(function ($model) {
            if ($model->is_submitted && ! $model->getOriginal('is_submitted')) {
                $model->submitted_at = Carbon::now();
            }
        });
    }

    public function terrafund_due_submissionable()
    {
        return $this->morphTo();
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function scopeForTerrafundNursery($query)
    {
        return $query->where('terrafund_due_submissionable_type', TerrafundNursery::class);
    }

    public function scopeForTerrafundSite($query)
    {
        return $query->where('terrafund_due_submissionable_type', TerrafundSite::class);
    }

    public function scopeUnsubmitted($query)
    {
        return $query->where('is_submitted', false);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('is_submitted', true);
    }

    public function scopeDueInFuture($query)
    {
        return $query->where('due_at', '>', now());
    }
}
