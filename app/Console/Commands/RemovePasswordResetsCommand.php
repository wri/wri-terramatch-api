<?php

namespace App\Console\Commands;

use App\Models\PasswordReset as PasswordResetModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class RemovePasswordResetsCommand extends Command
{
    protected $signature = 'remove-password-resets';

    protected $description = 'Removes password resets older than 7 days';

    public function handle(): int
    {
        $past = new DateTime('now - 7 days', new DateTimeZone('UTC'));
        PasswordResetModel::where('created_at', '<=', $past)->delete();

        return 0;
    }
}
