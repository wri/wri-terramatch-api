<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundProgrammeInvite as TerrafundProgrammeInviteModel;
use App\Resources\Resource;

class TerrafundProgrammeInviteResource extends Resource
{
    public function __construct(TerrafundProgrammeInviteModel $programmeInvite)
    {
        $this->id = $programmeInvite->id;
        $this->email_address = $programmeInvite->email_address;
        $this->terrafund_programme_id = $programmeInvite->terrafund_programme_id;
        $this->created_at = $programmeInvite->created_at;
    }
}
