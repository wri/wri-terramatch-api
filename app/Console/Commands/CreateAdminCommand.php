<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin as AdminModel;
use App\Validators\AdminValidator;
use Exception;
use DateTime;

class CreateAdminCommand extends Command
{
    protected $signature = "create-admin {firstName} {lastName} {emailAddress} {password} {jobRole}";
    protected $description = "Creates an admin";

    private $adminModel = null;
    private $adminValidator = null;

    public function __construct(AdminModel $adminModel, AdminValidator $adminValidator)
    {
        parent::__construct();
        $this->adminModel = $adminModel;
        $this->adminValidator = $adminValidator;
    }

    public function handle(): int
    {
        $data = [
            "first_name" => $this->argument("firstName"),
            "last_name" => $this->argument("lastName"),
            "email_address" => $this->argument("emailAddress"),
            "password" => $this->argument("password"),
            "job_role" => $this->argument("jobRole")
        ];
        try {
            $this->adminValidator->validate("create", $data);
        } catch (Exception $exception) {
            foreach ($exception->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->error(json_decode($error)[3]);
                }
            }
            return 1;
        }
        $data["role"] = "admin";
        $data["email_address_verified_at"] = new DateTime();
        $admin = $this->adminModel->newInstance($data);
        $admin->saveOrFail();
        return 0;
    }
}
