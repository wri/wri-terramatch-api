<?php

use Illuminate\Database\Seeder;

class FilterRecordsTableSeeder extends Seeder
{
    public function run()
    {
        $verification = new \App\Models\FilterRecord();
        $verification->user_id = 1;
        $verification->organisation_id = 1;
        $verification->type = 'pitches';
        $verification->saveOrFail();
    }
}
