<?php

namespace Database\Seeders;

use App\Models\SocioeconomicBenefit;
use Illuminate\Database\Seeder;

class SocioeconomicBenefitsTableSeeder extends Seeder
{
    public function run()
    {
        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name';
        $benefit->programme_id = 1;
        $benefit->site_id = null;
        $benefit->saveOrFail();

        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name2';
        $benefit->site_submission_id = 1;
        $benefit->saveOrFail();

        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name3';
        $benefit->site_id = 1;
        $benefit->site_submission_id = 1;
        $benefit->saveOrFail();

        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name4';
        $benefit->programme_submission_id = 1;
        $benefit->saveOrFail();

        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name5';
        $benefit->programme_submission_id = 2;
        $benefit->saveOrFail();
    }
}
