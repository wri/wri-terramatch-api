<?php

namespace App\Resources;

use App\Models\ProgrammeInvite as ProgrammeInviteModel;

class ProgrammeInviteResource extends Resource
{
    public function __construct(ProgrammeInviteModel $programmeInvite)
    {
        $this->id = $programmeInvite->id;
        $this->email_address = $programmeInvite->email_address;
        $this->accepted_at = $programmeInvite->accepted_at;
        $this->programme_id = $programmeInvite->programme_id;
        $this->created_at = $programmeInvite->created_at;
    }
}
