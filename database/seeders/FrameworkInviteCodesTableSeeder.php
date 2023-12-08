<?php

namespace Database\Seeders;

use App\Models\FrameworkInviteCode;
use Illuminate\Database\Seeder;

class FrameworkInviteCodesTableSeeder extends Seeder
{
    public function run()
    {
        $frameworkInviteCode = new FrameworkInviteCode();
        $frameworkInviteCode->id = 1;
        $frameworkInviteCode->code = 'kcs0611';
        $frameworkInviteCode->framework_id = 1; // PPC
        $frameworkInviteCode->saveOrFail();

        $frameworkInviteCode = new FrameworkInviteCode();
        $frameworkInviteCode->id = 2;
        $frameworkInviteCode->code = 'kcs0708';
        $frameworkInviteCode->framework_id = 1; // PPC
        $frameworkInviteCode->saveOrFail();

        $frameworkInviteCode = new FrameworkInviteCode();
        $frameworkInviteCode->id = 3;
        $frameworkInviteCode->code = 'kcs0509';
        $frameworkInviteCode->framework_id = 2; // Terrafund
        $frameworkInviteCode->saveOrFail();
    }
}
