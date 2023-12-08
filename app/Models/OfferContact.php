<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class OfferContact extends Model
{
    use NamedEntityTrait;

    public $fillable = [
        'offer_id',
        'user_id',
        'team_member_id',
    ];

    public $timestamps = false;

    public function team_member()
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
