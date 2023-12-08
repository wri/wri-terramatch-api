<?php

namespace App\Console\Commands;

use App\Models\Verification as VerificationModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class RemoveVerificationsCommand extends Command
{
    protected $signature = 'remove-verifications';

    protected $description = 'Removes verifications older than 48 hours';

    public function handle(): int
    {
        $past = new DateTime('now - 48 hours', new DateTimeZone('UTC'));
        VerificationModel::where('created_at', '<=', $past)->delete();

        return 0;
    }
}
