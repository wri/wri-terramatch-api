<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    public $fillable = [
        'programme_id',
        'programme_submission_id',
        'total_rows',
        'status',
    ];

    public $casts = [
        'has_failed' => 'boolean',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function programmeTreeSpecies()
    {
        return $this->hasMany(ProgrammeTreeSpecies::class);
    }
}
