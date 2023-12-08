<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgrammeTreeSpecies extends Model
{
    protected $fillable = [
        'programme_id',
        'programme_submission_id',
        'csv_import_id',
        'name',
        'amount',
    ];

    public function programme()
    {
        $this->belongsTo(Programme::class);
    }
}
