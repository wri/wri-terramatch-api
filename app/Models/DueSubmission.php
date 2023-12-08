<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DueSubmission extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'due_submissionable_type',
        'due_submissionable_id',
        'due_at',
        'is_submitted',
    ];

    protected $with = ['drafts'];

    public $casts = [
        'is_submitted' => 'boolean',
        'due_at' => 'datetime',
    ];

    public function due_submissionable()
    {
        return $this->morphTo();
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function scopeForProgramme($query)
    {
        return $query->where('due_submissionable_type', Programme::class);
    }

    public function scopeForSite($query)
    {
        return $query->where('due_submissionable_type', Site::class);
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
