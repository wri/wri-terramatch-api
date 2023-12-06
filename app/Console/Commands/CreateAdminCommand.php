<?php

namespace App\Console\Commands;

use App\Models\Admin as AdminModel;
use App\Validators\AdminValidator;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Console\Command;

class CreateAdminCommand extends Command
{
    protected $signature = 'create-admin {firstName} {lastName} {emailAddress} {password} {jobRole}';

    protected $description = 'Creates an admin';

    public function handle(): int
    {
        $data = [
            'first_name' => $this->argument('firstName'),
            'last_name' => $this->argument('lastName'),
            'email_address' => $this->argument('emailAddress'),
            'password' => $this->argument('password'),
            'job_role' => $this->argument('jobRole'),
        ];

        try {
            AdminValidator::validate('CREATE', $data);
        } catch (Exception $exception) {
            foreach ($exception->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->error(json_decode($error)[3]);
                }
            }

            return 1;
        }
        $data['role'] = 'admin';
        $data['email_address_verified_at'] = new DateTime('now', new DateTimeZone('UTC'));
        $admin = new AdminModel($data);
        $admin->saveOrFail();

        return 0;
    }
}
