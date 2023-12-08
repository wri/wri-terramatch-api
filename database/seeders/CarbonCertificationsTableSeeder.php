<?php

namespace Database\Seeders;

use App\Models\CarbonCertification as CarbonCertificationModel;
use Illuminate\Database\Seeder;

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
