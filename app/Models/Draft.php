<?php

namespace App\Models;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use NamedEntityTrait;
    use HasFactory;

    public $fillable = [
        'organisation_id',
        'due_submission_id',
        'name',
        'type',
        'data',
        'is_from_mobile',
        'is_merged',
        'created_by',
        'updated_by',
    ];

    public $casts = [
        'is_from_mobile' => 'boolean',
        'is_merged' => 'boolean',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function dueSubmission()
    {
        return $this->belongsToMany(DueSubmission::class);
    }

    public function terrafundDueSubmission()
    {
        return $this->belongsTo(TerrafundDueSubmission::class);
    }
}
