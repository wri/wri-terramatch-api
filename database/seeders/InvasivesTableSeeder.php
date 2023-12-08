<?php

namespace Database\Seeders;

use App\Models\Invasive;
use Illuminate\Database\Seeder;

class InvasivesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $invasive = new Invasive();
        $invasive->name = 'test invasive';
        $invasive->type = 'uncommon';
        $invasive->site_id = 1;
        $invasive->saveOrFail();
    }
}
