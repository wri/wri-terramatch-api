<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgrammeInvite extends Model
{
    public $fillable = [
        'email_address',
        'token',
        'accepted_at',
        'programme_id',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
}
