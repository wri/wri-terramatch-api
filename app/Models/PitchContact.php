<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class PitchContact extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'pitch_id',
        'user_id',
        'team_member_id',
    ];

    public $timestamps = false;

    public function team_member()
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
