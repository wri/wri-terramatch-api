<?php

use Illuminate\Database\Seeder;
use App\Models\Organisation as OrganisationModel;

class OrganisationsTableSeeder extends Seeder
{
    public function run()
    {
        $organisation = new OrganisationModel();
        $organisation->id = 1;
        $organisation->saveOrFail();

        $organisation = new OrganisationModel();
        $organisation->id = 2;
        $organisation->saveOrFail();
    }
}
