<?php

namespace Database\Seeders;

use App\Models\User as UserModel;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new UserModel();
        $user->id = 1;
        $user->uuid = Str::uuid();
        $user->first_name = 'Joe';
        $user->last_name = 'Doe';
        $user->email_address = 'joe@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 2;
        $user->uuid = Str::uuid();
        $user->first_name = 'Jane';
        $user->last_name = 'Doe';
        $user->email_address = 'jane@example.com';
        $user->password = 'Password123';
        $user->email_address_verified_at = new DateTime();
        $user->role = 'admin';
        $user->job_role = 'Manager';
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 3;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Steve';
        $user->last_name = 'Doe';
        $user->email_address = 'steve@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user->frameworks()->attach(1);
        $user->programmes()->attach(1);
        $user->refresh();

        $user = new UserModel();
        $user->id = 4;
        $user->uuid = Str::uuid();
        $user->first_name = 'Andrew';
        $user->last_name = 'Doe';
        $user->email_address = 'andrew@example.com';
        $user->password = 'Password123';
        $user->email_address_verified_at = new DateTime();
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->organisation_id = 2;
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 5;
        $user->email_address = 'tom@example.com';
        $user->role = 'admin';
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 6;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Dominic';
        $user->last_name = 'Doe';
        $user->email_address = 'dominic@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Supervisor';
        $user->phone_number = '9876543210';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 7;
        $user->uuid = Str::uuid();
        $user->email_address = 'sue@example.com';
        $user->role = 'user';
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 8;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'John';
        $user->last_name = 'Monitor';
        $user->email_address = 'monitoring.partner.1@monitor.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 9;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Dave';
        $user->last_name = 'Monitor';
        $user->email_address = 'monitoring.partner.2@monitor.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 10;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Steve';
        $user->last_name = 'Monitor';
        $user->email_address = 'monitoring.partner.3@monitor.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->uuid = Str::uuid();
        $user->avatar = null;
        $user->saveOrFail();
        $user->programmes()->sync([1]);

        $user = new UserModel();
        $user->id = 11;
        $user->uuid = Str::uuid();
        $user->organisation_id = 3;
        $user->first_name = 'Ian';
        $user->last_name = 'Doe';
        $user->email_address = 'ian@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->uuid = Str::uuid();
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 12;
        $user->organisation_id = 1;
        $user->first_name = 'Terrafund';
        $user->last_name = 'Doe';
        $user->email_address = 'terrafund@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();
        $user->frameworks()->attach(2);
        $user->terrafundProgrammes()->attach(1);
        $user->refresh();

        $user = new UserModel();
        $user->id = 13;
        $user->uuid = Str::uuid();
        $user->first_name = 'Terrafund Orphan';
        $user->last_name = 'Doe';
        $user->email_address = 'terrafund.orphan@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();
        $user->frameworks()->attach(2);
        $user->refresh();

        $user = new UserModel();
        $user->id = 14;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Terrafund';
        $user->last_name = 'New';
        $user->email_address = 'new.terrafund@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();
        $user->frameworks()->attach(2);
        $user->refresh();

        $user = new UserModel();
        $user->id = 15;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Terrafund';
        $user->last_name = 'Admin';
        $user->email_address = 'terrafund.admin@example.com';
        $user->password = 'Password123';
        $user->role = 'terrafund_admin';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();
        $user->frameworks()->attach(2);
        $user->refresh();

        $user = new UserModel();
        $user->id = 16;
        $user->uuid = Str::uuid();
        $user->organisation_id = 1;
        $user->first_name = 'Terrafund';
        $user->last_name = 'Second User';
        $user->email_address = 'terrafund.2@example.com';
        $user->password = 'Password123';
        $user->role = 'user';
        $user->job_role = 'Manager';
        $user->phone_number = '0123456789';
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->uuid = Str::uuid();
        $user->saveOrFail();
        $user->frameworks()->attach(2);
        $user->terrafundProgrammes()->attach(1);
        $user->terrafundProgrammes()->attach(2);
        $user->refresh();
    }
}
