<?php

use App\Models\Organisation as OrganisationModel;
use Illuminate\Database\Seeder;

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
