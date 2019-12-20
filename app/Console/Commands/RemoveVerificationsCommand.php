<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Verification as VerificationModel;
use DateTime;
use DateTimeZone;

class RemoveVerificationsCommand extends Command
{
    protected $signature = "remove-verifications";
    protected $description = "Removes verifications older than 48 hours";

    private $verificationModel = null;
    
    public function __construct(VerificationModel $verificationModel)
    {
        parent::__construct();
        $this->verificationModel = $verificationModel;
    }

    public function handle()
    {
        $past = new DateTime("now - 48 hours", new DateTimeZone("UTC"));
        $this->verificationModel->where("created_at", "<=", $past)->delete();
    }
}
