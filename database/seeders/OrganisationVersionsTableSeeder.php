<?php

namespace Database\Seeders;

use App\Models\OrganisationVersion as OrganisationVersionModel;
use Illuminate\Database\Seeder;

class OrganisationVersionsTableSeeder extends Seeder
{
    public function run()
    {
        $organisationVersion = new OrganisationVersionModel();
        $organisationVersion->id = 1;
        $organisationVersion->name = 'Acme Corporation';
        $organisationVersion->description = 'Lorem ipsum dolor sit amet';
        $organisationVersion->address_1 = '1 Foo Road';
        $organisationVersion->address_2 = null;
        $organisationVersion->city = 'Bar Town';
        $organisationVersion->state = 'Baz State';
        $organisationVersion->zip_code = 'Qux';
        $organisationVersion->country = 'GB';
        $organisationVersion->phone_number = '0123456789';
        $organisationVersion->website = 'http://www.example.com';
        $organisationVersion->avatar = null;
        $organisationVersion->cover_photo = null;
        $organisationVersion->organisation_id = 1;
        $organisationVersion->status = 'approved';
        $organisationVersion->approved_rejected_by = 2;
        $organisationVersion->saveOrFail();

        $organisationVersion = new OrganisationVersionModel();
        $organisationVersion->id = 2;
        $organisationVersion->name = 'Acme Corporation 2';
        $organisationVersion->description = 'Lorem ipsum dolor sit amet';
        $organisationVersion->address_1 = '1 Foo Road';
        $organisationVersion->address_2 = null;
        $organisationVersion->city = 'Bar Town';
        $organisationVersion->state = 'Baz State';
        $organisationVersion->zip_code = 'Qux';
        $organisationVersion->country = 'GB';
        $organisationVersion->phone_number = '+44123456789';
        $organisationVersion->website = 'https://www.example.com';
        $organisationVersion->avatar = null;
        $organisationVersion->cover_photo = null;
        $organisationVersion->organisation_id = 1;
        $organisationVersion->saveOrFail();

        $organisationVersion = new OrganisationVersionModel();
        $organisationVersion->id = 3;
        $organisationVersion->name = 'Foo Corporation';
        $organisationVersion->description = 'Lorem ipsum dolor sit amet';
        $organisationVersion->address_1 = '2 Foo Road';
        $organisationVersion->address_2 = null;
        $organisationVersion->city = 'Bar Town';
        $organisationVersion->state = 'Baz State';
        $organisationVersion->zip_code = 'Qux';
        $organisationVersion->country = 'GB';
        $organisationVersion->phone_number = '0123456789';
        $organisationVersion->website = 'http://www.example.org';
        $organisationVersion->avatar = null;
        $organisationVersion->cover_photo = null;
        $organisationVersion->organisation_id = 2;
        $organisationVersion->status = 'approved';
        $organisationVersion->approved_rejected_by = 2;
        $organisationVersion->saveOrFail();

        $organisationVersion = new OrganisationVersionModel();
        $organisationVersion->id = 4;
        $organisationVersion->name = 'Acme Corporation';
        $organisationVersion->description = 'Lorem ipsum dolor sit amet';
        $organisationVersion->address_1 = '1 Foo Road';
        $organisationVersion->address_2 = null;
        $organisationVersion->city = 'Bar Town';
        $organisationVersion->state = 'Baz State';
        $organisationVersion->zip_code = 'Qux';
        $organisationVersion->country = 'GB';
        $organisationVersion->phone_number = '0123456789';
        $organisationVersion->website = 'http://www.example.com';
        $organisationVersion->avatar = null;
        $organisationVersion->cover_photo = null;
        $organisationVersion->organisation_id = 1;
        $organisationVersion->status = 'rejected';
        $organisationVersion->approved_rejected_by = 2;
        $organisationVersion->saveOrFail();

        $organisationVersion = new OrganisationVersionModel();
        $organisationVersion->id = 5;
        $organisationVersion->name = 'Terrafund Corporation 2';
        $organisationVersion->account_type = 'terrafund';
        $organisationVersion->description = 'Lorem ipsum dolor sit amet';
        $organisationVersion->address_1 = '1 Foo Road';
        $organisationVersion->address_2 = null;
        $organisationVersion->city = 'Bar Town';
        $organisationVersion->state = 'Baz State';
        $organisationVersion->zip_code = 'Qux';
        $organisationVersion->country = 'GB';
        $organisationVersion->phone_number = '+44123456789';
        $organisationVersion->website = 'https://www.example.com';
        $organisationVersion->avatar = null;
        $organisationVersion->cover_photo = null;
        $organisationVersion->organisation_id = 4;
        $organisationVersion->saveOrFail();
    }
}
