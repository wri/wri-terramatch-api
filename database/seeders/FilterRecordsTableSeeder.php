<?php

namespace Database\Seeders;

use App\Models\FilterRecord as FilterRecordModel;
use Illuminate\Database\Seeder;

class FilterRecordsTableSeeder extends Seeder
{
    public function run()
    {
        $verification = new FilterRecordModel();
        $verification->id = 1;
        $verification->user_id = 1;
        $verification->organisation_id = 1;
        $verification->type = 'pitches';
        $verification->saveOrFail();
    }
}
