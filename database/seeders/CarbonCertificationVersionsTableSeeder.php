<?php

namespace Database\Seeders;

use App\Models\CarbonCertificationVersion as CarbonCertificationVersionModel;
use Illuminate\Database\Seeder;

class CarbonCertificationVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $carbonCertificationVersion = new CarbonCertificationVersionModel();
        $carbonCertificationVersion->id = 1;
        $carbonCertificationVersion->carbon_certification_id = 1;
        $carbonCertificationVersion->status = 'approved';
        $carbonCertificationVersion->type = 'fsc';
        $carbonCertificationVersion->link = 'http://example.org/carbon_certification.doc';
        $carbonCertificationVersion->saveOrFail();

        $carbonCertificationVersion = new CarbonCertificationVersionModel();
        $carbonCertificationVersion->id = 2;
        $carbonCertificationVersion->carbon_certification_id = 1;
        $carbonCertificationVersion->status = 'pending';
        $carbonCertificationVersion->type = 'fsc';
        $carbonCertificationVersion->link = 'http://example.org/carbon_certification_2.doc';
        $carbonCertificationVersion->saveOrFail();

        $carbonCertificationVersion = new CarbonCertificationVersionModel();
        $carbonCertificationVersion->id = 3;
        $carbonCertificationVersion->carbon_certification_id = 1;
        $carbonCertificationVersion->status = 'rejected';
        $carbonCertificationVersion->type = 'fsc';
        $carbonCertificationVersion->link = 'http://example.org/carbon_certification_2.doc';
        $carbonCertificationVersion->saveOrFail();
    }
}
