<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundProgrammeInvite;
use Illuminate\Database\Seeder;

class TerrafundProgrammeInvitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $terrafundProgrammeInvite = new TerrafundProgrammeInvite();
        $terrafundProgrammeInvite->id = 1;
        $terrafundProgrammeInvite->terrafund_programme_id = 1;
        $terrafundProgrammeInvite->email_address = 'new.terrafund@example.com';
        $terrafundProgrammeInvite->token = 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj';
        $terrafundProgrammeInvite->saveOrFail();
    }
}
