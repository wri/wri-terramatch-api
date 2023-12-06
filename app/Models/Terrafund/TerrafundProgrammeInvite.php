<?php

namespace App\Models\Terrafund;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerrafundProgrammeInvite extends Model
{
    use HasFactory;

    public $fillable = [
        'email_address',
        'terrafund_programme_id',
        'token',
    ];

    public function terrafundProgramme()
    {
        return $this->belongsTo(TerrafundProgramme::class);
    }
}
