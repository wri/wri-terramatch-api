<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordReset as PasswordResetsModel;
use DateTime;
use DateTimeZone;

class RemovePasswordResetsCommand extends Command
{
    protected $signature = "remove-password-resets";
    protected $description = "Removes password resets older than 2 hours";

    private $passwordResetsModel = null;

    public function __construct(PasswordResetsModel $passwordResetsModel)
    {
        parent::__construct();
        $this->passwordResetsModel = $passwordResetsModel;
    }

    public function handle()
    {
        $past = new DateTime("now - 2 hours", new DateTimeZone("UTC"));
        $this->passwordResetsModel->where("created_at", "<=", $past)->delete();
    }
}
