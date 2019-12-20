<?php

use Illuminate\Database\Seeder;
use App\Models\User as UserModel;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new UserModel();
        $user->id = 1;
        $user->first_name = "Joe";
        $user->last_name = "Doe";
        $user->email_address = "joe@example.com";
        $user->password = "Password123";
        $user->role = "user";
        $user->job_role = "Manager";
        $user->phone_number = "0123456789";
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 2;
        $user->first_name = "Jane";
        $user->last_name = "Doe";
        $user->email_address = "jane@example.com";
        $user->password = "Password123";
        $user->email_address_verified_at = new DateTime();
        $user->role = "admin";
        $user->job_role = "Manager";
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 3;
        $user->organisation_id = 1;
        $user->first_name = "Steve";
        $user->last_name = "Doe";
        $user->email_address = "steve@example.com";
        $user->password = "Password123";
        $user->role = "user";
        $user->job_role = "Manager";
        $user->phone_number = "0123456789";
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 4;
        $user->first_name = "Andrew";
        $user->last_name = "Doe";
        $user->email_address = "andrew@example.com";
        $user->password = "Password123";
        $user->email_address_verified_at = new DateTime();
        $user->role = "user";
        $user->job_role = "Manager";
        $user->organisation_id = 2;
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 5;
        $user->email_address = "tom@example.com";
        $user->role = "admin";
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 6;
        $user->organisation_id = 1;
        $user->first_name = "Dominic";
        $user->last_name = "Doe";
        $user->email_address = "dominic@example.com";
        $user->password = "Password123";
        $user->role = "user";
        $user->job_role = "Supervisor";
        $user->phone_number = "9876543210";
        $user->email_address_verified_at = new DateTime();
        $user->avatar = null;
        $user->saveOrFail();

        $user = new UserModel();
        $user->id = 7;
        $user->email_address = "sue@example.com";
        $user->role = "user";
        $user->saveOrFail();
    }
}
