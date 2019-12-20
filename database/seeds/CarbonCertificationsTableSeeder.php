<?php

use Illuminate\Database\Seeder;
use App\Models\CarbonCertification as CarbonCertificationModel;

class CarbonCertificationsTableSeeder extends Seeder
{
    public function run()
    {
        $carbonCertification = new CarbonCertificationModel();
        $carbonCertification->id = 1;
        $carbonCertification->pitch_id = 1;
        $carbonCertification->saveOrFail();
    }
}
