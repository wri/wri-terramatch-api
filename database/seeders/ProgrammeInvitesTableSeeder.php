<?php

namespace Database\Seeders;

use App\Models\ProgrammeInvite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgrammeInvitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $monitoringPartner = new ProgrammeInvite();
        $monitoringPartner->id = 1;
        $monitoringPartner->programme_id = 1;
        $monitoringPartner->email_address = 'monitoring.partner.1@monitor.com';
        $monitoringPartner->token = 'tlvOSFc5kpR2VqrCUiwI3gabz5OeLr7LdUmhyyF693agCu7fyW9d8p4pBtEGORmj';
        $monitoringPartner->accepted_at = null;
        $monitoringPartner->saveOrFail();

        $monitoringPartner = new ProgrammeInvite();
        $monitoringPartner->id = 2;
        $monitoringPartner->programme_id = 1;
        $monitoringPartner->email_address = 'monitoring.partner.2@monitor.com';
        $monitoringPartner->token = Str::random(64);
        $monitoringPartner->accepted_at = null;
        $monitoringPartner->saveOrFail();

        $monitoringPartner = new ProgrammeInvite();
        $monitoringPartner->id = 3;
        $monitoringPartner->programme_id = 2;
        $monitoringPartner->email_address = 'monitoring.partner.1@monitor.com';
        $monitoringPartner->token = 'QhiKk66GX9fkLaEZY06T6KLEw8ALhPkeBtmN5e9wgNo48cSmmhlRlFrczRjLtz3S';
        $monitoringPartner->accepted_at = now()->subMonth();
        $monitoringPartner->saveOrFail();
    }
}
