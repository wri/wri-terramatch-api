<?php

namespace Database\Seeders;

use App\Models\Framework;
use Illuminate\Database\Seeder;

class FrameworksTableSeeder extends Seeder
{
    public function run()
    {
        $framework = new Framework();
        $framework->id = 1;
        $framework->name = 'PPC';
        $framework->slug = 'ppc';
        $framework->saveOrFail();

        $framework = new Framework();
        $framework->id = 2;
        $framework->name = 'Terrafund';
        $framework->slug = 'terrafund';
        $framework->saveOrFail();
    }
}
